<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Ynlo\GraphQLBundle\Subscription\PubSub\PubSubHandlerInterface;
use Ynlo\GraphQLBundle\Subscription\Subscriber;

class SubscriptionsController
{
    /**
     * @var string
     */
    protected $mercureHubUrl;

    /**
     * @var PubSubHandlerInterface
     */
    protected $pubSubHandler;

    /**
     * @var int
     */
    protected $ttl = Subscriber::DEFAULT_SUBSCRIPTION_TTL;

    /**
     * SubscriptionsController constructor.
     *
     * @param PubSubHandlerInterface $pubSubHandler
     */
    public function __construct(PubSubHandlerInterface $pubSubHandler)
    {
        $this->pubSubHandler = $pubSubHandler;
    }

    /**
     * @param int $ttl
     *
     * @return SubscriptionsController
     */
    public function setTtl(int $ttl): SubscriptionsController
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @param array  $mercureHubsUrls
     * @param string $hub
     */
    public function setMercureHubUrl(array $mercureHubsUrls, $hub)
    {
        $this->mercureHubUrl = $mercureHubsUrls[$hub];
    }

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, string $subscription)
    {
        if (!$this->pubSubHandler->exists($subscription)) {
            throw new NotFoundHttpException();
        }

        $this->pubSubHandler->touch($subscription, date_create_from_format('U', time() + $this->ttl));

        return new RedirectResponse(sprintf('%s?topic=%s', $this->mercureHubUrl, $subscription));
    }
}
