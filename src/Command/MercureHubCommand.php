<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Command;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Ynlo\GraphQLBundle\Subscription\SubscriptionManager;

/**
 * This command act as a middleware to execute the mercure hub process in order to
 * listen conected/disconected subscribers to remove subscriptions without any active subscriber
 */
class MercureHubCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

    protected bool $debug;

    public function __construct(SubscriptionManager $subscriptionManager, bool $debug = false)
    {
        $this->subscriptionManager = $subscriptionManager;
        $this->debug = $debug;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('graphql:mercure:start')
             ->setDescription('Start mercure HUB server. Define mercure settings using env variables with MERCURE_* prefix in your .env file')
             ->addOption('mercure', null, InputOption::VALUE_REQUIRED, 'Use custom mercure binary file otherwise the integrated binary will be used')
             ->addOption('caddyFile', null, InputOption::VALUE_REQUIRED, 'Use custom configuration file')
             ->addOption('serverName', null, InputOption::VALUE_REQUIRED, 'Server name and port to listen (if use port 443 with specific server name a Let\'s Encrypt TLS certificate will automatically generated) ', ':4000')
             ->addOption('transportUrl', null, InputOption::VALUE_REQUIRED, 'URL representation of the transport to use. Use local://local to disabled history, (example bolt:///var/run/mercure.db?size=100&cleanup_frequency=0.4)')
             ->addOption('corsOrigins', null, InputOption::VALUE_REQUIRED, 'Allow subscribers with no valid JWT to connect')
             ->addOption('anonymous', null, InputOption::VALUE_NONE, 'Allow subscribers with no valid JWT to connect')
             ->addOption('demo', null, InputOption::VALUE_NONE, 'Enable the debug UI and expose demo endpoints');

    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mercure = $input->getOption('mercure');
        if (!$mercure) {
            $mercure = __DIR__.'/../Resources/mercure/mercure';
        }

        $caddyFile = $input->getOption('caddyFile');
        if (!$caddyFile) {
            $caddyFile = __DIR__.'/../Resources/mercure/Caddyfile';
        }

        $env = [
            'MERCURE_SERVER_NAME' => $input->getOption('serverName'),
            'MERCURE_DEBUG' => $this->debug ? 'debug' : '',
            'MERCURE_ANONYMOUS' => $input->getOption('anonymous') ? 'anonymous' : '',
            'MERCURE_DEMO' => $input->getOption('demo') ? 'demo' : '',
        ];

        if ($transportUrl = $input->getOption('transportUrl')) {
            $env['MERCURE_TRANSPORT_URL'] = $transportUrl;
        }

        if ($corsOrigins = $input->getOption('corsOrigins')) {
            $env['MERCURE_CORS_ORIGINS'] = sprintf('cors_origins %s', $corsOrigins);
        }

        foreach ($_ENV as $name => $value) {
            if (preg_match('/^MERCURE_(\w+)$/', $name, $matches)) {
                $env[$matches[1]] = $value;
            }
        }

        $process = new Process([$mercure, 'run', '-config', $caddyFile], null, $env, null, null);

        $subscriptionManager = $this->subscriptionManager;
        $subscriptionManager->subscriptionBucket()->clear();

        $subscribersByTopics = [];

        $logger = $this->logger;
        if ($logger) {
            $logger->info('Initializing mercure...');
        }

        $process->run(
            static function ($type, $msg) use ($output, &$subscribersByTopics, $subscriptionManager, $logger) {
                $output->writeln($msg);

                $connected = strpos($msg, '"New subscriber"') !== false;
                $disconnected = strpos($msg, '"Subscriber disconnected"') !== false;

                if ($connected || $disconnected) {
                    preg_match('/"remote_addr":"([^"]+)"/', $msg, $matches);
                    $remoteAddr = $matches[1] ?? null;

                    preg_match('/topics":\["([\w+-]+)"/', $msg, $matches);
                    $subscription = $matches[1] ?? null;

                    if ($logger) {
                        if ($connected) {
                            $logger->info(sprintf('Client connected to subscription: %s from IP: %s', $subscription, $remoteAddr));
                        } else {
                            $logger->info(sprintf('Client disconnected from subscription: %s from IP: %s', $subscription, $remoteAddr));
                        }
                    }

                    if ($subscription && $remoteAddr) {
                        if ($connected) {
                            if (!isset($subscribersByTopics[$subscription])) {
                                $subscribersByTopics[$subscription] = [];
                            }

                            $subscriptionManager->subscriptionBucket()->hit($subscription);
                            $subscribersByTopics[$subscription][$remoteAddr] = true;

                            if ($logger) {
                                $logger->info(sprintf('Hit subscription: %s ', $subscription));
                            }

                        } elseif ($disconnected) {
                            if (isset($subscribersByTopics[$subscription][$remoteAddr])) {
                                unset($subscribersByTopics[$subscription][$remoteAddr]);
                            }

                            if (empty($subscribersByTopics[$subscription])) {
                                unset($subscribersByTopics[$subscription]);
                                $subscriptionManager->subscriptionBucket()->remove($subscription);

                                if ($logger) {
                                    $logger->info(sprintf('Delete subscription: %s ', $subscription));
                                }
                            }
                        }
                    }
                }
            }
        );

        return 0;
    }
}
