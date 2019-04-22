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
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 6379;
        $this->prefix = $config['prefix'] ?? 'GraphQLSubscription:';

        $this->client = new \Redis();
        $this->client->connect($host, $port);
        $this->client->setOption(\Redis::OPT_PREFIX, $this->prefix);

        $this->consumer = new \Redis();
        $this->consumer->connect($host, $port);
        $this->consumer->setOption(\Redis::OPT_PREFIX, $this->prefix);
    }

    /**
     * @inheritDoc
     */
    public function sub(string $channel, string $id, array $meta, \DateTime $expireAt = null): void
    {
        $key = sprintf('%s:%s', $channel, $id);
        $alreadyExists = $this->exists($id);
        $ttl = $this->client->ttl($key);
        $this->client->set($key, serialize($meta));

        if ($alreadyExists) {
            if ($expireAt && $expireAt->getTimestamp() - $ttl > time()) {
                $this->client->expireAt($key, $expireAt->format('U'));
            } else {
                $this->client->setTimeout($key, $ttl);
            }
        } elseif ($expireAt) {
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
    public function touch(string $id, \DateTime $expireAt): void
    {
        $iterator = null;
        while ($iterator !== 0) {
            while ($keys = $this->client->scan($iterator, "*:$id*")) {
                foreach ($keys as $key) {
                    $key = $this->unprefix($key);
                    $this->client->expireAt($key, $expireAt->format('U'));
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
        while ($iterator !== 0) {
            while ($keys = $this->client->scan($iterator, "*:$id*")) {
                $this->client->del($keys);
            }
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
        try {
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
        } catch (\RedisException $redisException) {
            $this->consume($channels, $dispatch);
        }
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
