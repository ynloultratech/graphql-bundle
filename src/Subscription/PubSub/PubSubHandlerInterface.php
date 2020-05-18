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

interface PubSubHandlerInterface
{
    /**
     * Create a new subscription using given ID and subscribe to given channel
     * with given metadata
     *
     * @param string         $channel  channel to subscribe
     * @param string         $id       id of the subscription
     * @param array          $meta     serializable array to store subscription metadata
     * @param \DateTime|null $expireAt the subscription should me marked using this date
     */
    public function sub(string $channel, string $id, array $meta, \DateTime $expireAt = null): void;

    /**
     * Dispatch all subscriptions in given channel
     * and pass given arguments.
     *
     * @param string $channel
     * @param array  $filters
     * @param array  $data
     */
    public function pub(string $channel, array $filters = [], array $data = []): void;

    /**
     * Mark given subscription as subscribed and remove expiration date
     *
     * @param string    $id
     */
    public function touch(string $id): void;

    /**
     * Delete given subscription
     *
     * @param string $id
     */
    public function del(string $id): void;

    /**
     * Clear all subscriptions
     */
    public function clear(): void;

    /**
     * Delete given subscription
     *
     * @param string $id
     *
     * @return bool
     */
    public function exists(string $id): bool;

    /**
     * Launch a listener and wait for subscriptions updates
     * When the listener detect a update in some channel must call the dispatch function
     *
     * @param array    $channels list of channels to listen for changes
     * @param callable $dispatch function to execute when a change is published
     *                           this function must be called with a SubscriptionMessage as argument
     */
    public function consume(array $channels, callable $dispatch): void;
}
