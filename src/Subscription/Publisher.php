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

class Publisher
{
    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

    /**
     * SubscriptionManager constructor.
     *
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @param string $subscription
     * @param array  $filters
     * @param array  $data
     */
    public function publish(string $subscription, $filters = [], array $data = []): void
    {
        $this->subscriptionManager->publish($subscription, $filters, $data);
    }
}
