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

use Symfony\Component\Messenger\MessageBusInterface;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Subscription\Bucket\SubscriptionBucketInterface;

class SubscriptionManager
{
    protected DefinitionRegistry $registry;

    protected MessageBusInterface $messageBus;

    protected SubscriptionBucketInterface $subscriptionBucket;

    protected string $secret;

    public function __construct(DefinitionRegistry $definitionRegistry, MessageBusInterface $messageBus, SubscriptionBucketInterface $subscriptionBucket, string $secret)
    {
        $this->registry = $definitionRegistry;
        $this->subscriptionBucket = $subscriptionBucket;
        $this->messageBus = $messageBus;
        $this->secret = $secret;
    }

    public function subscriptionBucket(): SubscriptionBucketInterface
    {
        return $this->subscriptionBucket;
    }

    public function subscribe(Subscription $subscription, \DateTime $expireAt = null): void
    {
        $this->subscriptionBucket->add($subscription, $expireAt);
    }

    /**
     * Publish update for all existent subscriptions in given channel
     *
     * @param string $channel subscription name or class
     * @param array  $filters array of filters to compare with subscriptions
     * @param array  $data    data to submit to the subscription
     */
    public function publish(string $channel, array $filters = [], array $data = []): void
    {
        $resolvers = array_flip($this->registry->getEndpoint()->getSubscriptionsResolvers());
        if (isset($resolvers[$channel])) {
            $channel = $resolvers[$channel];
        }

        // send a SubscriptionDispatch event to message queue
        $this->messageBus->dispatch(new SubscriptionPublish($channel, $filters, $data));
    }
}
