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
use Doctrine\Common\Persistence\Proxy;
use GraphQL\Deferred;
use GraphQL\Error\Error;
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
use Ynlo\GraphQLBundle\Events\GraphQLFieldInfo;
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
     * @var int[]
     */
    private static $concurrentUsages = [];

    /**
     * @var DeferredBuffer
     */
    protected $deferredBuffer;

    /**
     * ObjectFieldResolver constructor.
     *
     * @param ContainerInterface $container
     * @param DeferredBuffer     $deferredBuffer
     */
    public function __construct(ContainerInterface $container, DeferredBuffer $deferredBuffer)
    {
        $this->container = $container;
        $this->deferredBuffer = $deferredBuffer;
    }

    /**
     * @param mixed                 $root
     * @param array                 $args
     * @param FieldExecutionContext $context
     * @param ResolveInfo           $info
     *
     * @return mixed|null|string
     *
     * @throws \Exception
     */
    public function __invoke($root, array $args, FieldExecutionContext $context, ResolveInfo $info)
    {
        $value = null;
        $fieldDefinition = $context->getDefinition()->getField($info->fieldName);
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);

        $fieldInfo = new GraphQLFieldInfo($context->getDefinition(), $fieldDefinition, $info);
        $event = new GraphQLFieldEvent(
            $fieldInfo,
            $root,
            $args,
            $context
        );
        $eventDispatcher->dispatch(GraphQLEvents::PRE_READ_FIELD, $event);

        if ($event->isPropagationStopped() || $event->getValue()) {
            $eventDispatcher->dispatch(GraphQLEvents::POST_READ_FIELD, $event);

            return $event->getValue();
        }

        $this->verifyConcurrentUsage($context->getQueryContext(), $fieldDefinition);

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

            $resolver = new ResolverExecutor($this->container, $context->getQueryContext()->getEndpoint(), $queryDefinition);
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

        if ($value instanceof Proxy && $value instanceof NodeInterface && !$value->__isInitialized()) {
            $this->deferredBuffer->add($value);

            return new Deferred(
                function () use ($value) {
                    $this->deferredBuffer->loadBuffer();

                    return $this->deferredBuffer->getLoadedEntity($value);
                }
            );
        }

        $event->setValue($value);
        $eventDispatcher->dispatch(GraphQLEvents::POST_READ_FIELD, $event);

        return $event->getValue();
    }

    /**
     * @param QueryExecutionContext $context
     * @param FieldDefinition       $definition
     *
     * @throws Error
     */
    private function verifyConcurrentUsage(QueryExecutionContext $context, FieldDefinition $definition)
    {
        if ($maxConcurrentUsage = $definition->getMaxConcurrentUsage()) {
            $oid = spl_object_hash($definition);
            $usages = static::$concurrentUsages[$context->getQueryId()][$oid] ?? 1;
            if ($usages > $maxConcurrentUsage) {
                if (1 === $maxConcurrentUsage) {
                    $error = sprintf(
                        'The field "%s" can be fetched only once per query. This field can`t be used in a list.',
                        $definition->getName()
                    );
                } else {
                    $error = sprintf(
                        'The field "%s" can`t be fetched more than %s times per query.',
                        $definition->getName(),
                        $maxConcurrentUsage
                    );
                }
                throw new Error($error);
            }
            static::$concurrentUsages[$context->getQueryId()][$oid] = $usages + 1;
        }
    }
}
