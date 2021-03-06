<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type;

/**
 * Class SubscriptionType
 */
class SubscriptionType extends QueryType
{
    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'name' => 'Subscription',
            'fields' => function () {
                $subscriptions = [];
                foreach ($this->endpoint->allSubscriptions() as $subscription) {
                    $subscriptions[$subscription->getName()] = $this->getQueryConfig($subscription);
                }

                return $subscriptions;
            },
        ];

        parent::__construct(array_merge($defaults, $config));
    }
}
