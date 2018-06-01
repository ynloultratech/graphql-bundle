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
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLFieldEvent;
use Ynlo\GraphQLBundle\Events\GraphQLMutationEvent;
use Ynlo\GraphQLBundle\Exception\GraphQL\ForbiddenFieldException;
use Ynlo\GraphQLBundle\Exception\GraphQL\ForbiddenObjectException;
use Ynlo\GraphQLBundle\Exception\GraphQL\SecurityException;
use Ynlo\GraphQLBundle\Security\Authorization\AccessControlChecker;

class AccessControlListener implements EventSubscriberInterface
{
    /**
     * @var AccessControlChecker
     */
    protected $accessControlChecker;

    /**
     * @param AccessControlChecker $accessControlChecker
     */
    public function __construct(AccessControlChecker $accessControlChecker)
    {
        $this->accessControlChecker = $accessControlChecker;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            GraphQLEvents::POST_READ_FIELD => 'preReadField',
            GraphQLEvents::MUTATION_SUBMITTED => 'onSubmitMutation',
        ];
    }

    public function onSubmitMutation(GraphQLMutationEvent $event)
    {
        $operation = $event->getContext()->getDefinition();
        if ($this->accessControlChecker->isControlled($operation)
            && !$this->accessControlChecker->isGranted($operation, $event->getFormEvent()->getData())
        ) {
            $message = $this->accessControlChecker->getMessage($operation) ?? 'Access Denied';
            throw new SecurityException($message);
        }
    }

    public function preReadField(GraphQLFieldEvent $event)
    {
        //check firstly if the user have rights to read the object
        $object = $event->getInfo()->getObject();
        if ($this->accessControlChecker->isControlled($object)
            && !$this->accessControlChecker->isGranted($object, $event->getRoot())
        ) {
            throw new ForbiddenObjectException(
                $event->getInfo()->getObject(),
                $this->accessControlChecker->getMessage($object)
            );
        }

        //check then if the user have rights to read the field
        $field = $event->getInfo()->getField();
        if ($this->accessControlChecker->isControlled($field)
            && !$this->accessControlChecker->isGranted($field, $event->getRoot())
        ) {
            throw new ForbiddenFieldException(
                $event->getInfo()->getObject(),
                $event->getInfo()->getField(),
                $this->accessControlChecker->getMessage($field)
            );
        }
    }
}
