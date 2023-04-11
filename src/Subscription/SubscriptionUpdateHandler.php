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

use Firebase\JWT\JWT;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SubscriptionUpdateHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function __invoke(SubscriptionUpdate $update)
    {
        if ($this->logger) {
            $this->logger->info(sprintf('Subscription UPDATE event received for subscription (^%s) in channel: %s', $update->getSubscription()->getId(), $update->getSubscription()->getChannel()));
        }

        $originRequest = $update->getSubscription()->getRequest();
        $subscriptionToken = JWT::encode(
            [
                'jti' => $update->getSubscription()->getId(),
                'iat' => time(),
                'exp' => time() + 60,
                'data' => serialize($update->getPublish()->getData()),
            ],
            $this->secret,
            'HS256'
        );

        $ch = curl_init($originRequest->getUri());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $originRequest->getContent());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $headers = [
            'Content-Type:application/json',
            sprintf('Subscription:%s', $subscriptionToken),
        ];

        if ($auth = $originRequest->headers->get('Authorization')) {
            $headers[] = sprintf('Authorization:%s', $auth);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_exec($ch);

        $errorNo = curl_errno($ch);
        $errorMessage = curl_error($ch);
        curl_close($ch);
        if ($errorNo) {
            throw new \RuntimeException($errorMessage);
        } else {
            $this->logger->info(sprintf('Subscription sent successfully for subscription %s', $update->getSubscription()->getId()));
        }
    }
}