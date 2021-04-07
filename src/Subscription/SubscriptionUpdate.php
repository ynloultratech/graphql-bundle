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

namespace Ynlo\GraphQLBundle\Subscription;

/**
 * message triggered to send a update for each subscription after publish a update
 */
class SubscriptionUpdate implements SubscriptionMessage
{
    protected Subscription $subscription;
    protected SubscriptionPublish $publish;

    /**
     * SubscriptionUpdate constructor.
     *
     * @param Subscription        $subscription
     * @param SubscriptionPublish $dispatch
     */
    public function __construct(Subscription $subscription, SubscriptionPublish $dispatch)
    {
        $this->subscription = $subscription;
        $this->publish = $dispatch;
    }

    /**
     * @return Subscription
     */
    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    /**
     * @return SubscriptionPublish
     */
    public function getPublish(): SubscriptionPublish
    {
        return $this->publish;
    }
}