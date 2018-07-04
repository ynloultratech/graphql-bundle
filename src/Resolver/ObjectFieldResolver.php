<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Resolver;

use Doctrine\Common\Collections\Collection;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLFieldEvent;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Type\Types;
use Ynlo\GraphQLBundle\Util\IDEncoder;

/**
 * Default resolver for all object fields
 */
class ObjectFieldResolver implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * ObjectFieldResolver constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param mixed           $root
     * @param array           $args
     * @param ResolverContext $context
     * @param ResolveInfo     $info
     *
     * @return mixed|null|string
     *
     * @throws \Exception
     */
    public function __invoke($root, array $args, ResolverContext $context, ResolveInfo $info)
    {
        $value = null;
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $fieldDefinition = $context->getDefinition();

        if (!$fieldDefinition instanceof FieldDefinition) {
            throw new \RuntimeException('This resolver can only resolve fields');
        }

        $event = new GraphQLFieldEvent($context);
        $eventDispatcher->dispatch(GraphQLEvents::PRE_READ_FIELD, $event);

        if ($event->isPropagationStopped() || $event->getValue()) {
            $eventDispatcher->dispatch(GraphQLEvents::POST_READ_FIELD, $event);

            return $event->getValue();
        }

        //when use external resolver or use a object method with arguments
        if (($resolver = $fieldDefinition->getResolver()) || $fieldDefinition->getArguments()) {
            $queryDefinition = new QueryDefinition();
            $queryDefinition->setName($fieldDefinition->getName());
            $queryDefinition->setType($fieldDefinition->getType());
            $queryDefinition->setNode($fieldDefinition->getNode());
            $queryDefinition->setArguments($fieldDefinition->getArguments());
            $queryDefinition->setList($fieldDefinition->isList());
            $queryDefinition->setMetas($fieldDefinition->getMetas());

            if ($resolver) {
                $queryDefinition->setResolver($resolver);
            } elseif ($fieldDefinition->getOriginType() === \ReflectionMethod::class) {
                $queryDefinition->setResolver($fieldDefinition->getOriginName());
            }

            $context = ContextBuilder::create($context->getEndpoint())
                                     ->setArgs($args)
                                     ->setRoot($root)
                                     ->setDefinition($queryDefinition)
                                     ->setResolveInfo($info)
                                     ->build();

            $resolver = new ResolverExecutor($this->container, $queryDefinition);
            $value = $resolver($root, $args, $context, $info);
        } else {
            $accessor = new PropertyAccessor(true);
            $originName = $fieldDefinition->getOriginName() ?: $fieldDefinition->getName();
            $value = $accessor->getValue($root, $originName);
        }

        if (null !== $value && Types::ID === $fieldDefinition->getType() && $root instanceof NodeInterface) {
            //ID are formed with base64 representation of the Types and real database ID
            //in order to create a unique and global identifier for each resource
            //@see https://facebook.github.io/relay/docs/graphql-object-identification.html
            $value = IDEncoder::encode($root);
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        $event->setValue($value);
        $eventDispatcher->dispatch(GraphQLEvents::POST_READ_FIELD, $event);

        return $event->getValue();
    }
}
