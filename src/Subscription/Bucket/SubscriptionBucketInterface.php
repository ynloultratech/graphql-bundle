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

interface SubscriptionBucketInterface
{
    /**
     * Save subscription
     *
     * @param Subscription   $subscription subscription
     * @param \DateTime|null $expireAt     the subscription should me marked using this date
     */
    public function add(Subscription $subscription, \DateTime $expireAt = null): void;

    /**
     * @param string $channel
     *
     * @return iterable|Subscription[]
     */
    public function all(string $channel): iterable;

    /**
     * Mark given subscription as subscribed and remove expiration date
     *
     * @param string $id
     */
    public function hit(string $id): void;

    /**
     * Delete given subscription
     *
     * @param string $id
     */
    public function remove(string $id): void;

    /**
     * Clear all subscriptions
     */
    public function clear(): void;

    /**
     * Exist given subscription
     *
     * @param string $id
     *
     * @return bool
     */
    public function exists(string $id): bool;
}
