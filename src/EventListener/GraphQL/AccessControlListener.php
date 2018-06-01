<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\EventListener\GraphQL;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLFieldEvent;
use Ynlo\GraphQLBundle\Exception\GraphQL\ForbiddenFieldException;
use Ynlo\GraphQLBundle\Exception\GraphQL\ForbiddenObjectException;

class AccessControlListener implements EventSubscriberInterface
{
    /**
     * @var ExpressionVoter
     */
    protected $voter;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationChecker
     */
    protected $authChecker;

    /**
     * AccessControlListener constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authChecker = $authorizationChecker;
    }


    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            GraphQLEvents::POST_READ_FIELD => 'preReadField',
        ];
    }

    public function preReadField(GraphQLFieldEvent $event)
    {
        //check firstly if the user have rights to read the object
        $objectAccessControl = $event->getInfo()->getObject()->getMeta('access_control');
        if ($objectAccessControl && $objectExpression = $objectAccessControl['expression'] ?? null) {
            if ($expressionSerialized = $objectAccessControl['expression_serialized'] ?? null) {
                $expression = unserialize($expressionSerialized, ['allowed_classes' => true]);
            } else {
                $expression = new Expression($objectExpression);
            }

            if (!$this->authChecker->isGranted($expression, $event->getRoot())) {
                $message = $objectAccessControl['message'] ?? null;
                throw new ForbiddenObjectException($event->getInfo()->getObject(), $message);
            }
        }

        //check then if the user have rights to read the field
        $fieldAccessControl = $event->getInfo()->getField()->getMeta('access_control');
        if ($fieldAccessControl && $fieldExpression = $fieldAccessControl['expression'] ?? null) {
            if ($expressionSerialized = $fieldAccessControl['expression_serialized'] ?? null) {
                $expression = unserialize($expressionSerialized, ['allowed_classes' => true]);
            } else {
                $expression = new Expression($fieldExpression);
            }

            if (!$this->authChecker->isGranted($expression, $event->getRoot())) {
                $message = $fieldAccessControl['message'] ?? null;
                throw new ForbiddenFieldException($event->getInfo()->getObject(), $event->getInfo()->getField(), $message);
            }
        }
    }
}
