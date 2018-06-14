<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\EventListener\JWT;

use GraphQL\Error\Error;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Error\ErrorFormatterInterface;
use Ynlo\GraphQLBundle\Error\ErrorHandlerInterface;
use Ynlo\GraphQLBundle\Exception\Controlled\UnauthorizedError;

class AuthenticationFailureListener implements EventSubscriberInterface
{
    /**
     * @var ErrorFormatterInterface
     */
    protected $errorFormatter;

    /**
     * @var ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @deprecated Since v1.1 and will will be removed in the next mayor release
     *
     * @var bool
     */
    protected $JWTCompatibility = false;

    /**
     * AuthenticationFailureListener constructor.
     *
     * @param ErrorFormatterInterface $errorFormatter
     * @param ErrorHandlerInterface   $errorHandler
     */
    public function __construct(ErrorFormatterInterface $errorFormatter, ErrorHandlerInterface $errorHandler)
    {
        $this->errorFormatter = $errorFormatter;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @deprecated Since v1.1 and will will be removed in the next mayor release
     *
     * @param bool $JWTCompatibility
     */
    public function setJWTCompatibility(bool $JWTCompatibility): void
    {
        $this->JWTCompatibility = $JWTCompatibility;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        if (!class_exists('Lexik\Bundle\JWTAuthenticationBundle\Events')) {
            return [];
        }

        return [
            Events::AUTHENTICATION_FAILURE => 'onAuthFailure',
            Events::JWT_EXPIRED => 'onAuthFailure',
            Events::JWT_INVALID => 'onAuthFailure',
            Events::JWT_NOT_FOUND => 'onAuthFailure',
        ];
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthFailure(AuthenticationFailureEvent $event)
    {
        $error = Error::createLocatedError(new UnauthorizedError($event->getException()->getMessage()));
        $errors = $this->errorHandler->handle([$error], $this->errorFormatter, $this->debug);

        $responseArray = [
            'errors' => $errors,
        ];

        if ($this->JWTCompatibility) {
            @trigger_error('The JWT error compatibility has been deprecated and will be removed in the next mayor release, migrate your clients to the new error format.', E_USER_DEPRECATED);
            $responseArray = array_merge(
                [
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => $event->getException()->getMessage(),
                ],
                $responseArray
            );
        }

        $event->getResponse()->setContent(json_encode($responseArray));
    }
}
