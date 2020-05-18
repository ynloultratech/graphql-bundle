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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Ynlo\GraphQLBundle\Subscription\SubscriptionManager;

/**
 * This command act as a middleware to execute the mercure hub process in order to
 * listen conected/disconected subscribers to remove subscriptions without any active subscriber
 */
class MercureHubCommand extends Command
{
    /**
     * @var string
     */
    protected $secret;

    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

    /**
     * MercureHubCommand constructor.
     *
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('graphql:mercure:start')
             ->setDescription('Start mercure HUB server. Define mercure settings using env variables with MERCURE_* prefix in your .env file')
             ->addArgument('mercure', InputArgument::REQUIRED, 'Mercure binary');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = [
            'ALLOW_ANONYMOUS' => 1,
            'CORS_ALLOWED_ORIGINS' => '*',
        ];

        foreach ($_ENV as $name => $value) {
            if (preg_match('/^MERCURE_(\w+)$/', $name, $matches)) {
                $env[$matches[1]] = $value;
            }
        }

        $process = new Process([$input->getArgument('mercure')], null, $env, null, null);

        $subscriptionManager = $this->subscriptionManager;
        $subscriptionManager->handler()->clear();

        $subscribersByTopics = [];

        $process->run(
            static function ($type, $msg) use ($output, &$subscribersByTopics, $subscriptionManager) {
                $output->writeln($msg);

                $connected = strpos($msg, '"New subscriber"') !== false;
                $disconnected = strpos($msg, '"Subscriber disconnected"') !== false;

                if ($connected || $disconnected) {
                    preg_match('/remote_addr="([^"]+)"/', $msg, $matches);
                    $remoteAddr = $matches[1] ?? null;

                    preg_match('/subscriber_topics="\[([^]]+)/', $msg, $matches);
                    $subscription = $matches[1] ?? null;

                    if ($subscription && $remoteAddr) {
                        if ($connected) {
                            if (!isset($subscribersByTopics[$subscription])) {
                                $subscribersByTopics[$subscription] = [];
                            }

                            $subscriptionManager->handler()->touch($subscription);
                            $subscribersByTopics[$subscription][$remoteAddr] = true;
                        } elseif ($disconnected) {
                            if (isset($subscribersByTopics[$subscription][$remoteAddr])) {
                                unset($subscribersByTopics[$subscription][$remoteAddr]);
                            }

                            if (empty($subscribersByTopics[$subscription])) {
                                unset($subscribersByTopics[$subscription]);
                                $subscriptionManager->handler()->del($subscription);
                            }
                        }
                    }
                }
            }
        );
    }
}
