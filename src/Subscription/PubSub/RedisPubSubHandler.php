<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Subscription\PubSub;

class RedisPubSubHandler implements PubSubHandlerInterface
{
    /**
     * @var string
     */
    protected $redisHost;

    /**
     * @var string
     */
    protected $redisPort;

    /**
     * @var \Redis
     */
    protected $client;

    /**
     * @var \Redis
     */
    protected $consumer;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * RedisPubSubHandler constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->redisHost = $config['host'] ?? 'localhost';
        $this->redisPort = $config['port'] ?? 6379;
        $this->prefix = $config['prefix'] ?? 'GraphQLSubscription:';

        $this->client = new \Redis();
        $this->client->connect($this->redisHost, $this->redisPort);
        $this->client->setOption(\Redis::OPT_PREFIX, $this->prefix);
    }

    /**
     * @inheritDoc
     */
    public function sub(string $channel, string $id, array $meta, \DateTime $expireAt = null): void
    {
        $key = sprintf('%s:%s', $channel, $id);
        $alreadyExists = $this->exists($id);
        $this->client->set($key, serialize($meta));

        if (!$expireAt) {
            $expireAt = new \DateTime('+24Hours');
        }
        if (!$alreadyExists && $expireAt) {
            $this->client->expireAt($key, $expireAt->format('U'));
        }

        $iterator = null;
    }

    /**
     * @inheritDoc
     */
    public function pub(string $channel, array $filters = [], array $data = []): void
    {
        $this->client->publish($channel, serialize([$filters, $data]));
    }

    /**
     * @inheritDoc
     */
    public function touch(string $id): void
    {
        $iterator = null;
        while ($iterator !== 0) {
            while ($keys = $this->client->scan($iterator, "*:$id*")) {
                foreach ($keys as $key) {
                    $this->client->expireAt($this->unprefix($key), (new \DateTime('+24Hours'))->format('U'));
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function del(string $id): void
    {
        $iterator = null;
        foreach ($this->client->keys("*:$id") as $key) {
            $this->client->del($this->unprefix($key));
        }
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $iterator = null;
        foreach ($this->client->keys('*') as $key) {
            $this->client->del($this->unprefix($key));
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(string $id): bool
    {
        $iterator = null;
        while ($iterator !== 0) {
            $keys = $this->client->scan($iterator, "*:$id*");
            if (!empty($keys)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function consume(array $channels, callable $dispatch): void
    {
        $this->consumer = new \Redis();
        // the timeout is specified to avoid redis connection error after some time running the consumer
        // the `default_socket_timeout` to -1 like described here https://github.com/phpredis/phpredis/issues/70
        // can't be used because create a conflict with others sock open functions like used in Ynlo\GraphQLBundle\Subscription\SubscriptionManager::sendRequest
        $this->consumer->connect($this->redisHost, $this->redisPort, 0, null, 0, 100000);
        $this->consumer->setOption(\Redis::OPT_READ_TIMEOUT, 1000000);
        $this->consumer->setOption(\Redis::OPT_PREFIX, $this->prefix);
        $this->consumer->subscribe(
            $channels,
            function (\Redis $redis, $chan, $event) use ($dispatch) {
                $iterator = null;
                while ($iterator !== 0) {
                    while ($keys = $this->client->scan($iterator, "*$chan:*", 100)) {
                        [$filters, $data] = unserialize($event, [true]);
                        foreach ($keys as $key) {
                            $key = $this->unprefix($key);
                            $chan = $this->unprefix($chan);
                            $meta = $this->client->get($key);
                            preg_match("/$chan:(.+)/", $key, $matches);
                            if ($matches) {
                                $message = new SubscriptionMessage(
                                    $chan,
                                    $matches[1],
                                    $data,
                                    $filters,
                                    unserialize($meta, [true])
                                );

                                $dispatch($message);
                            }
                        }
                    }
                }
            }
        );
    }

    /**
     * Helper to remove prefix
     *
     * @param string $key
     *
     * @return string
     */
    private function unprefix($key): string
    {
        return str_replace($this->prefix, null, $key);
    }
}
