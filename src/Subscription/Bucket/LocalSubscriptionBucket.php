<?php
/*
 * ******************************************************************************
 * This file is part of the GraphQL Bundle package.
 *
 * (c) YnloUltratech <support@ynloultratech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  *****************************************************************************
 */

namespace Ynlo\GraphQLBundle\Subscription\Bucket;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\PruneableInterface;
use Ynlo\GraphQLBundle\Subscription\Subscription;

class LocalSubscriptionBucket implements SubscriptionBucketInterface
{
    protected AdapterInterface $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    public function add(Subscription $subscription, \DateTime $expireAt = null): void
    {
        $channelSubscriptionsCache = $this->cache->getItem($subscription->getChannel());
        if (!$channelSubscriptionsCache->isHit()) {
            $channelSubscriptionsCache->set([$subscription->getId()]);
        } else {
            $channelSubscriptionsCache->set(array_unique(array_merge($channelSubscriptionsCache->get(), [$subscription->getId()])));
        }

        $this->cache->save($channelSubscriptionsCache);

        $subscriptionCache = $this->cache->getItem($subscription->getId());

        if (!$subscriptionCache->isHit()) {
            $subscriptionCache->expiresAt($expireAt);
            $subscriptionCache->set($subscription);
            $this->cache->save($subscriptionCache);
        }
    }

    public function all(string $channel): iterable
    {
        $channelSubscriptionsCache = $this->cache->getItem($channel);
        if ($channelSubscriptionsCache->isHit()) {
            $channelSubscriptions = $channelSubscriptionsCache->get();
            foreach ($channelSubscriptions as $id) {
                $subscriptionCache = $this->cache->getItem($id);
                if ($subscriptionCache->isHit()) {
                    yield $subscriptionCache->get();
                }
            }
        }
    }

    public function hit(string $id): void
    {
        $subscriptionCache = $this->cache->getItem($id);
        if ($subscriptionCache->isHit()) {
            $subscriptionCache->expiresAt(null);
            $this->cache->save($subscriptionCache);
        }
    }

    public function remove(string $id): void
    {
        $subscriptionCache = $this->cache->getItem($id);
        if ($subscriptionCache->isHit()) {
            /** @var Subscription $subscription */
            $subscription = $subscriptionCache->get();
            $channelSubscriptionsCache = $this->cache->getItem($subscription->getChannel());
            if ($channelSubscriptionsCache->isHit()) {
                $channelSubscriptions = $channelSubscriptionsCache->get();
                if (($key = array_search($id, $channelSubscriptions)) !== false) {
                    unset($channelSubscriptions[$key]);
                }

                $channelSubscriptionsCache->set($channelSubscriptions);
                $this->cache->save($channelSubscriptionsCache);
            }

        }

        $this->cache->deleteItem($id);
    }

    public function clear(): void
    {
        if ($this->cache instanceof PruneableInterface) {
            $this->cache->clear();
        }
    }

    public function exists(string $id): bool
    {
        return $this->cache->getItem($id)->isHit();
    }
}