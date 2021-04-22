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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Ynlo\GraphQLBundle\Subscription\Bucket\SubscriptionBucketInterface;

class SubscriptionPublishHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected MessageBusInterface $messageBus;
    protected SubscriptionBucketInterface $subscriptionBucket;

    public function __construct(MessageBusInterface $messageBus, SubscriptionBucketInterface $subscriptionBucket)
    {
        $this->subscriptionBucket = $subscriptionBucket;
        $this->messageBus = $messageBus;
    }

    public function __invoke(SubscriptionPublish $publish)
    {
        $this->logger->info(sprintf('Subscription PUBLISH event received from channel: %s', $publish->getChannel()));
        foreach ($this->subscriptionBucket->all($publish->getChannel()) as $subscription) {
            /** @var Request $request */
            $subscribedArguments = $subscription->getArguments();
            $subscribedChannel = $subscription->getChannel();
            if ($subscribedChannel === $publish->getChannel()
                && $this->matchFilters($subscribedArguments, $publish->getFilters())) {
                $this->logger->info(sprintf('Dispatching subscription (%s) UPDATE for channel: %s', $subscription->getId(), $publish->getChannel()));
                $this->messageBus->dispatch(new SubscriptionUpdate($subscription, $publish));
            }
        }
    }

    /**
     * @param array $subscribed
     * @param array $filters
     *
     * @return bool
     */
    private function matchFilters(array $subscribed, array $filters): bool
    {
        foreach ($subscribed as $subProperty => $subValue) {
            if (isset($filters[$subProperty])) {
                $filterValue = $filters[$subProperty];

                if (is_array($subValue) && in_array($filterValue, $subValue, true)) {
                    continue;
                }

                if ($subValue !== $filterValue) {
                    return false;
                }
            }
        }

        return true;
    }
}