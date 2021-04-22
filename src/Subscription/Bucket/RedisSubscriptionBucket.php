<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Subscription\Bucket;

use Ynlo\GraphQLBundle\Subscription\Subscription;

class RedisSubscriptionBucket implements SubscriptionBucketInterface
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
    }

    public function getClient(): \Redis
    {
        if (!$this->client) {
            $this->client = new \Redis();
            $this->client->connect($this->redisHost, $this->redisPort);
            $this->client->setOption(\Redis::OPT_PREFIX, $this->prefix);
        }

        return $this->client;
    }

    /**
     * @inheritDoc
     */
    public function add(Subscription $subscription, \DateTime $expireAt = null): void
    {
        $key = sprintf('%s:%s', $subscription->getChannel(), $subscription->getId());
        $alreadyExists = $this->exists($subscription->getId());
        $this->getClient()->set($key, serialize($subscription));

        if (!$expireAt) {
            $expireAt = new \DateTime('+24Hours');
        }
        if (!$alreadyExists && $expireAt) {
            $this->getClient()->expireAt($key, $expireAt->format('U'));
        }

        $iterator = null;
    }

    public function all(string $channel): iterable
    {
        $iterator = null;
        while ($iterator !== 0) {
            while ($keys = $this->getClient()->scan($iterator, "*$channel:*", 100)) {
                foreach ($keys as $key) {
                    $data = $this->getClient()->get($this->unprefix($key));
                    yield unserialize($data);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function hit(string $id): void
    {
        $iterator = null;
        while ($iterator !== 0) {
            while ($keys = $this->getClient()->scan($iterator, "*:$id*")) {
                foreach ($keys as $key) {
                    $this->getClient()->expireAt($this->unprefix($key), (new \DateTime('+24Hours'))->format('U'));
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function remove(string $id): void
    {
        $iterator = null;
        while ($iterator !== 0) {
            while ($keys = $this->getClient()->scan($iterator, "*:$id*")) {
                foreach ($keys as $key) {
                    // mark to expire instead of hard remove to allow re-connection on connection lost
                    $this->getClient()->expireAt($this->unprefix($key), (new \DateTime('+60Seconds'))->format('U'));
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $iterator = null;
        foreach ($this->getClient()->keys('*') as $key) {
            $this->getClient()->del($this->unprefix($key));
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(string $id): bool
    {
        $iterator = null;
        while ($iterator !== 0) {
            $keys = $this->getClient()->scan($iterator, "*:$id*");
            if (!empty($keys)) {
                return true;
            }
        }

        return false;
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
