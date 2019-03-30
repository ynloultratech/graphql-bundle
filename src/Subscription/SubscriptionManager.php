<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Subscription;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Subscription\PubSub\PubSubHandlerInterface;
use Ynlo\GraphQLBundle\Subscription\PubSub\SubscriptionMessage;
use Ynlo\GraphQLBundle\Util\IDEncoder;

class SubscriptionManager
{
    /**
     * @var DefinitionRegistry
     */
    protected $registry;

    /**
     * @var PubSubHandlerInterface
     */
    protected $pubSubHandler;

    /**
     * @var string
     */
    protected $secret;

    /**
     * SubscriptionManager constructor.
     *
     * @param DefinitionRegistry     $definitionRegistry
     * @param PubSubHandlerInterface $pubSubHandler
     * @param string                 $secret
     */
    public function __construct(DefinitionRegistry $definitionRegistry, PubSubHandlerInterface $pubSubHandler, string $secret)
    {
        $this->registry = $definitionRegistry;
        $this->pubSubHandler = $pubSubHandler;
        $this->secret = $secret;
    }

    /**
     * Subscribe given request to given subscription,
     * when this subscription is dispatched this request will be executed
     *
     * @param string         $id
     * @param string         $channel
     * @param array          $args
     * @param Request        $request
     * @param \DateTime|null $expireAt
     */
    public function subscribe($id, string $channel, $args, Request $request, \DateTime $expireAt = null): void
    {
        $this->convertNodes($args);
        $this->pubSubHandler->sub(
            $channel,
            $id,
            [
                'channel' => $channel,
                'arguments' => $args,
                'request' => $request,
            ],
            $expireAt
        );
    }

    /**
     * @param string $name    subscription name or class
     * @param array  $filters array of filters to compare with subscriptions
     * @param array  $data    data to submit to the subscription
     */
    public function publish(string $name, array $filters = [], array $data = []): void
    {
        $resolvers = array_flip($this->registry->getEndpoint()->getSubscriptionsResolvers());
        if (isset($resolvers[$name])) {
            $name = $resolvers[$name];
        }
        $this->convertNodes($filters);

        array_walk_recursive(
            $data,
            function (&$value, $key) {
                if (is_object($value)) {
                    throw new \RuntimeException(
                        sprintf('The object "%s" in key "%s" can\'t be part of publish data, only scalar values can be sent.', get_class($value), $key)
                    );
                }
            }
        );

        $this->pubSubHandler->pub($name, $filters, $data);
    }

    /**
     * @param OutputInterface $output
     * @param bool            $debug
     */
    public function consume(OutputInterface $output, $debug = false): void
    {
        $channels = array_keys($this->registry->getEndpoint()->getSubscriptionsResolvers());
        $this->pubSubHandler->consume(
            $channels,
            function (SubscriptionMessage $message) use ($output, $debug) {
                /** @var Request $request */
                $request = $message->getMeta()['request'];
                $subscribedFilters = $message->getMeta()['arguments'] ?? [];
                $subscribedChannel = $message->getMeta()['channel'] ?? null;
                if (($subscribedChannel === $message->getChannel())
                    && $this->matchFilters($subscribedFilters, $message->getFilters())) {
                    $output->writeln(sprintf('[INFO] Process subscription "%s" of channel "%s"', $message->getId(), $message->getChannel()));
                    $this->sendRequest($request, $message, $output, $debug);
                }
            }
        );
    }

    /**
     * @param array $subscribed
     * @param array $filters
     *
     * @return bool
     */
    private function matchFilters(array $subscribed, array $filters): bool
    {
        foreach ($subscribed as $subProperty => $subValue) {
            if (isset($filters[$subProperty])) {
                $filterValue = $filters[$subProperty];

                if (is_array($subValue) && in_array($filterValue, $subValue, true)) {
                    continue;
                }

                if ($subValue !== $filterValue) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Convert nodes to ID
     *
     * @param array $data
     */
    private function convertNodes(array &$data): void
    {
        array_walk_recursive(
            $data,
            function (&$value) {
                if ($value instanceof NodeInterface) {
                    $value = IDEncoder::encode($value);
                }
            }
        );
    }

    /**
     * Send a subscription request
     *
     * @param Request             $originRequest
     * @param SubscriptionMessage $message
     * @param OutputInterface     $output
     * @param boolean             $debug
     */
    private function sendRequest(Request $originRequest, SubscriptionMessage $message, OutputInterface $output, bool $debug = false): void
    {
        $host = $originRequest->getHost();
        $port = $originRequest->getPort();
        $path = $originRequest->getPathInfo();

        $handle = fsockopen($host, $port, $errno, $errstr, 10);

        $signer = new Sha256();
        $subscriptionToken = (new Builder())->setId($message->getId())
                                            ->set('data', serialize($message->getData()))
                                            ->setIssuedAt(time())
                                            ->setNotBefore(time() + 60)
                                            ->setExpiration(time() + 60)
                                            ->sign($signer, $this->secret)
                                            ->getToken();

        $body = $originRequest->getContent();
        $length = strlen($body);
        $out = "POST $path HTTP/1.1\r\n";
        $out .= "Host: $host\r\n";
        $auth = $originRequest->headers->get('Authorization');
        $out .= "Authorization: $auth\r\n";
        $out .= "Subscription: $subscriptionToken\r\n";
        $out .= "Content-Length: $length\r\n";
        $out .= "Content-Type: application/json\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $out .= $body;
        fwrite($handle, $out);

        if ($debug) {
            /// in debug mode wait for response
            $output->writeln(sprintf('[DEBUG] Getting response for subscription %s', $message->getId()));
            while (true) {
                $buffer = fgets($handle);
                if (!$buffer) {
                    break;
                }

                $output->write($buffer);
            }
        }
        fclose($handle);
    }
}
