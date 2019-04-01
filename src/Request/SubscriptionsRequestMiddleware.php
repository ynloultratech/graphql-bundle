<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Request;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Symfony\Component\HttpFoundation\Request;
use Ynlo\GraphQLBundle\Subscription\SubscriptionRequest;

/**
 * This middleware listen for internal subscriptions requests to set this arguments in the query.
 *
 * NOTE: subscriptions requests are internal requests send by a subscription consumer in order to
 * emulate a request like a final user but when a subscription is dispatched.
 */
class SubscriptionsRequestMiddleware implements RequestMiddlewareInterface
{
    protected $secret;

    /**
     * SubscriptionsRequestMiddleware constructor.
     *
     * @param string $secret
     */
    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function processRequest(Request $request, ExecuteQuery $query): void
    {
        $content = $request->getContent();
        if ($content
            && $request->headers->has('Subscription')
            && $subscriptionJWT = $request->headers->get('Subscription')) {
            $token = (new Parser())->parse($subscriptionJWT);
            if (!$token->verify(new Sha256(), $this->secret)) {
                throw new \RuntimeException('Invalid subscription signature');
            }

            $query->setSubscriptionRequest(
                new SubscriptionRequest(
                    $token->getClaim('jti'),
                    unserialize($token->getClaim('data'), [true])
                )
            );
        }
    }
}
