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

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @GraphQL\ObjectType(description="
The subscription link have all required information to subscribe to server events.

Can susbribe to a service using something like:

    new EventSource(subscription.url)
")
 */
class SubscriptionLink
{
    /**
     * @var string
     *
     * @GraphQL\Field(type="string!", description="corresponding subscription url containing a unique subscription ID. The client can
subscribe to the event stream corresponding to this subscription by creating a `new EventSource`.")
     */
    protected $url;

    /**
     * SubscriptionLink constructor.
     *
     * @param string  $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
