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

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLOperationEvent;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;
use Ynlo\GraphQLBundle\Util\Uuid;

/**
 * The subscriber is a resolver that resolve subscriptions and return a subscription link
 * act as a middleware before the real subscription resolver is executed
 */
class Subscriber implements EventSubscriberInterface
{
    public const DEFAULT_SUBSCRIPTION_TTL = 300;

    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @var int
     */
    protected $subscriptionsTtl = self::DEFAULT_SUBSCRIPTION_TTL;

    /**
     * Subscriber constructor.
     *
     * @param RequestStack        $requestStack
     * @param Router              $router
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(RequestStack $requestStack, Router $router, ?SubscriptionManager $subscriptionManager = null)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @param int $subscriptionsTtl
     */
    public function setSubscriptionsTtl(int $subscriptionsTtl): void
    {
        $this->subscriptionsTtl = $subscriptionsTtl;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            GraphQLEvents::OPERATION_START => 'operationStart',
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onSymfonyAuthSuccess',
        ];
    }

    /**
     * @param AuthenticationEvent $event
     */
    public function onSymfonyAuthSuccess(AuthenticationEvent $event): void
    {
        $this->username = $event->getAuthenticationToken()->getUsername();
    }

    /**
     * @param GraphQLOperationEvent $event
     */
    public function operationStart(GraphQLOperationEvent $event): void
    {
        $this->endpoint = $event->getEndpoint();
    }

    /**
     * @param ResolverContext $context
     * @param array           $args
     *
     * @return SubscriptionLink
     *
     * @throws \Exception
     */
    public function __invoke(ResolverContext $context, $args = [])
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            throw new \RuntimeException('Missing required request');
        }

        // subscriptions are created with a very lowest expiration date
        // the user must use the subscription otherwise will me marked as expired
        $expireAt = new \DateTime('+20seconds');

        $id = Uuid::createFromData(
            [
                $this->username,
                $this->endpoint,
                $request->getUri(),
                $request->getContent(),
            ]
        );

        $subscriptionName = $this->endpoint->getSubscriptionNameForResolver($context->getDefinition()->getResolver());
        $this->subscriptionManager->subscribe($id, $subscriptionName, $args, $request, $expireAt);
        $url = $this->router->generate('api_subscriptions', ['subscription' => $id], Router::ABSOLUTE_URL);
        $heartbeatUrl = $this->router->generate('api_subscriptions_heartbeat', ['subscription' => $id], Router::ABSOLUTE_URL);

        return new SubscriptionLink($url, $heartbeatUrl, $this->subscriptionsTtl);
    }
}
