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
        $host = $originRequest->getHost();
        $port = $originRequest->getPort();
        $path = $originRequest->getPathInfo();

        $handle = fsockopen($originRequest->isSecure() ? 'ssl://'.$host : $host, $port, $errno, $errstr, 10);

        $subscriptionToken = JWT::encode(
            [
                'jti' => $update->getSubscription()->getId(),
                'iat' => time(),
                'exp' => time() + 60,
                'data' => serialize($update->getPublish()->getData()),
            ],
            $this->secret
        );

        $body = $originRequest->getContent();
        $length = strlen($body);
        $out = "POST $path HTTP/1.1\r\n";
        $out .= "Host: $host\r\n";
        $auth = $originRequest->headers->get('Authorization');
        $out .= "Authorization: $auth\r\n";
        $out .= "Subscription: $subscriptionToken\r\n";
        $out .= "Content-Length: $length\r\n";
        $out .= "Content-Type: application/json\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $out .= $body;
        fwrite($handle, $out);

        $emptyResponse = true;
        while (true) {
            $buffer = fgets($handle);
            if (!$buffer) {
                break;
            }
            $emptyResponse = false;
        }
        if ($emptyResponse) {
            if ($this->logger) {
                $this->logger->warning(sprintf('Empty response for subscription %s', $update->getSubscription()->getId()));
            }
        } else if ($this->logger) {
            $this->logger->info(sprintf('[INFO] Response received successfully for subscription %s', $update->getSubscription()->getId()));
        }
        fclose($handle);
    }
}