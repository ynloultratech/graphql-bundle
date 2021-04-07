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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
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
    /**
     * @var string
     */
    protected $subscriptionsUrl;

    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

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
     * Subscriber constructor.
     *
     * @param RequestStack        $requestStack
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(RequestStack $requestStack, ?SubscriptionManager $subscriptionManager = null)
    {
        $this->requestStack = $requestStack;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @param array  $mercureHubsUrls
     * @param string $hub
     */
    public function setSubscriptionsUrlFromHub(array $mercureHubsUrls, $hub)
    {
        $this->subscriptionsUrl = $mercureHubsUrls[$hub];
    }

    /**
     * @param string $subscriptionsUrl
     */
    public function setSubscriptionsUrl(string $subscriptionsUrl): void
    {
        $this->subscriptionsUrl = $subscriptionsUrl;
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

        if ($request->getSession()) {
            //clear session
            $request->setSession(new Session());
        }

        // subscriptions are created with a very lowest expiration date
        // if the client does not connect to given subscription in x seconds the subscription is automatically deleted
        $expireAt = new \DateTime('+10seconds');

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

        return new SubscriptionLink(sprintf('%s?topic=%s', $this->subscriptionsUrl, $id));
    }
}
