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

**NOTE:** A subscription have a tll (Time to live), you must call a periodic request (heartbeat) to
the `heartbeatUrl` in order to keep-alive the subscription. Otherwise the subscription will be not available
after the specified ttl.
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
     * @var string
     *
     * @GraphQL\Field(type="string!", description="Url to send periodicals requests (heartbeats) to keep-alive the subscription.")
     */
    protected $heartbeatUrl;

    /**
     * @var int
     *
     * @GraphQL\Field(type="int!", description="Time to live(in seconds) for a subscription, must call a heartbeat to keep the subscription alive")
     */
    protected $ttl;

    /**
     * SubscriptionLink constructor.
     *
     * @param string  $url
     * @param string  $heartbeatUrl
     * @param integer $ttl
     */
    public function __construct(string $url, $heartbeatUrl, int $ttl)
    {
        $this->url = $url;
        $this->heartbeatUrl = $heartbeatUrl;
        $this->ttl = $ttl;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getHeartbeatUrl(): string
    {
        return $this->heartbeatUrl;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}
