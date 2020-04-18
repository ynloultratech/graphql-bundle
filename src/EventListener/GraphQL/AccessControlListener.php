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
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLFieldEvent;
use Ynlo\GraphQLBundle\Events\GraphQLMutationEvent;
use Ynlo\GraphQLBundle\Exception\Controlled\ForbiddenError;
use Ynlo\GraphQLBundle\Security\Authorization\AccessControlChecker;
use Ynlo\GraphQLBundle\Util\TypeUtil;

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
            GraphQLEvents::PRE_READ_FIELD => 'preReadField',
            GraphQLEvents::MUTATION_SUBMITTED => 'onSubmitMutation',
        ];
    }

    public function onSubmitMutation(GraphQLMutationEvent $event)
    {
        $operation = $event->getContext()->getDefinition();
        if ($this->accessControlChecker->isControlled($operation)
            && !$this->accessControlChecker->isGranted($operation, $event->getFormEvent()->getData())
        ) {
            $message = $this->accessControlChecker->getMessage($operation) ?? null;
            throw new ForbiddenError($message);
        }
    }

    public function preReadField(GraphQLFieldEvent $event)
    {
        $definition = $event->getContext()->getDefinition();

        //check firstly if the user have rights to read the operation
        if ($definition instanceof QueryDefinition && $this->accessControlChecker->isControlled($definition)
            && !$this->accessControlChecker->isGranted($definition, $event->getContext()->getRoot())
        ) {
            $event->stopPropagation();
            $event->setValue(null);
            throw new ForbiddenError($this->accessControlChecker->getMessage($definition));
        }

        //check if user have rights to read the object
        if (\is_object($event->getContext()->getRoot())) {
            $concreteType = TypeUtil::resolveObjectType($event->getContext()->getEndpoint(), $event->getContext()->getRoot());

            if ($concreteType) {
                $objectDefinition = $event->getContext()->getEndpoint()->getType($concreteType);
                if ($this->accessControlChecker->isControlled($objectDefinition)
                    && !$this->accessControlChecker->isGranted($objectDefinition, $event->getContext()->getRoot())
                ) {
                    $event->stopPropagation();
                    $event->setValue(null);
                    throw new ForbiddenError($this->accessControlChecker->getMessage($objectDefinition));
                }

                // check access on interfaces
                if ($objectDefinition instanceof ObjectDefinition){
                    foreach ($objectDefinition->getInterfaces() as $interface){
                        $interfaceDef =$event->getContext()->getEndpoint()->getType($interface);
                        if ($this->accessControlChecker->isControlled($interfaceDef)
                            && !$this->accessControlChecker->isGranted($interfaceDef, $event->getContext()->getRoot())
                        ) {
                            $event->stopPropagation();
                            $event->setValue(null);
                            throw new ForbiddenError($this->accessControlChecker->getMessage($interfaceDef));
                        }
                    }
                }
            }
        }

        //check then if the user have rights to read the field
        if ($definition instanceof FieldDefinition && $this->accessControlChecker->isControlled($definition)
            && !$this->accessControlChecker->isGranted($definition, $event->getContext()->getRoot())
        ) {
            throw new ForbiddenError($this->accessControlChecker->getMessage($definition));
        }
    }
}
