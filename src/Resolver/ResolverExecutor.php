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

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityNotFoundException;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Ynlo\GraphQLBundle\Component\AutoWire\AutoWire;
use Ynlo\GraphQLBundle\Definition\ClassAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\HasExtensionsInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Definition\UnionDefinition;
use Ynlo\GraphQLBundle\Exception\Controlled\BadRequestError;
use Ynlo\GraphQLBundle\Exception\Controlled\NotFoundError;
use Ynlo\GraphQLBundle\Extension\ExtensionInterface;
use Ynlo\GraphQLBundle\Extension\ExtensionManager;
use Ynlo\GraphQLBundle\Extension\ExtensionsAwareInterface;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Subscription\AsynchronousJobInterface;
use Ynlo\GraphQLBundle\Subscription\FilteredSubscriptionInterface;
use Ynlo\GraphQLBundle\Subscription\Subscriber;
use Ynlo\GraphQLBundle\Subscription\SubscriptionEvent;
use Ynlo\GraphQLBundle\Subscription\SubscriptionRequest;
use Ynlo\GraphQLBundle\Type\Types;
use Ynlo\GraphQLBundle\Util\IDEncoder;

/**
 * This resolver act as a middleware between the executableDefinition and final resolvers.
 * Using injection of parameters can resolve the parameters needed by the final resolver before invoke
 */
class ResolverExecutor implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @var ExecutableDefinitionInterface
     */
    protected $executableDefinition;

    /**
     * @var mixed
     */
    protected $root;

    /**
     * @var ResolveInfo
     */
    protected $resolveInfo;

    /**
     * @var mixed
     */
    protected $context;

    /**
     * ResolverExecutor constructor.
     *
     * @param ContainerInterface            $container
     * @param ExecutableDefinitionInterface $executableDefinition
     */
    public function __construct(ContainerInterface $container, ExecutableDefinitionInterface $executableDefinition)
    {
        $this->container = $container;
        $this->executableDefinition = $executableDefinition;
    }

    /**
     * @param mixed           $root
     * @param array           $args
     * @param ResolverContext $context
     * @param ResolveInfo     $resolveInfo
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __invoke($root, array $args, ResolverContext $context, ResolveInfo $resolveInfo)
    {
        $this->root = $root;
        $this->resolveInfo = $resolveInfo;
        $this->endpoint = $context->getEndpoint();

        $resolverName = $this->executableDefinition->getResolver();

        $resolver = null;
        $refMethod = null;

        if (class_exists($resolverName)) {
            $refClass = new \ReflectionClass($resolverName);

            //Verify if exist a service with resolver name and use it
            //otherwise build the resolver using simple injection
            //@see Ynlo\GraphQLBundle\Component\AutoWire\AutoWire
            if ($this->container->has($resolverName)) {
                $resolver = $this->container->get($resolverName);
            } else {
                /** @var callable $resolver */
                $resolver = $this->container->get(AutoWire::class)->createInstance($refClass->getName());
            }

            if ($resolver instanceof ContainerAwareInterface) {
                $resolver->setContainer($this->container);
            }

            if ($refClass->hasMethod('__invoke')) {
                $refMethod = $refClass->getMethod('__invoke');
            }
        } elseif (method_exists($root, $resolverName)) {
            $resolver = $root;
            $refMethod = new \ReflectionMethod(ClassUtils::getClass($root), $resolverName);
        }

        /** @var SubscriptionRequest|null $subscriptionRequest */
        $subscriptionRequest = $context->getMeta('subscriptionRequest');

        $isSubscriptionSubscribe = $resolveInfo->operation->operation === 'subscription' && !$subscriptionRequest && !$resolver instanceof EmptyObjectResolver;
        $isSubscriptionRequest = $resolveInfo->operation->operation === 'subscription' && $subscriptionRequest && !$resolver instanceof EmptyObjectResolver;

        if ($resolver && $refMethod) {
            $this->context = ContextBuilder::create($context->getEndpoint())
                                           ->setRoot($root)
                                           ->setResolveInfo($resolveInfo)
                                           ->setArgs($args)
                                           ->setMetas($context->getMetas())
                                           ->setDefinition($this->executableDefinition)
                                           ->build();

            if ($resolver instanceof ResolverInterface) {
                $resolver->setContext($this->context);
            }

            $node = $this->context->getNode();
            if ($resolver instanceof ExtensionsAwareInterface && $node instanceof HasExtensionsInterface) {
                $resolver->setExtensions($this->resolveObjectExtensions($node));
            }

            if ($resolver instanceof AbstractResolver) {
                $resolver->setServices($this->container->get(ResolverServices::class));
            }

            if ($subscriptionRequest) {
                $args = array_merge($args, $subscriptionRequest->getData());
            }

            $params = $this->prepareMethodParameters($refMethod, $args, !$subscriptionRequest);

            // resolve filters
            $filters = [];
            if ($isSubscriptionSubscribe && $resolver instanceof FilteredSubscriptionInterface) {
                $filterMethod = new \ReflectionMethod(get_class($resolver), 'getFilters');
                $filters = $filterMethod->invoke($resolver);
            }

            // call the async queue
            if ($isSubscriptionSubscribe && $resolver instanceof AsynchronousJobInterface) {
                $asyncMethod = new \ReflectionMethod(get_class($resolver), 'onSubscribe');
                $asyncMethod->invokeArgs($resolver, $params);
            }

            if ($isSubscriptionSubscribe) {
                $resolver = $this->container->get(Subscriber::class);
                $result = $resolver->__invoke($this->context, array_merge($this->normalizeArguments($args), $filters));
            } else {
                $result = $refMethod->invokeArgs($resolver, $params);
            }

            if ($isSubscriptionRequest) {
                if (!$result) {
                    exit;
                }

                $subscriptionUnion = $this->endpoint->getType($this->executableDefinition->getType());
                $result = new SubscriptionEvent($result);
                if ($subscriptionUnion instanceof UnionDefinition) {
                    $result->setConcreteType(array_values($subscriptionUnion->getTypes())[0]->getType());
                }
            }

            return $result;
        }

        $error = sprintf('The resolver "%s" for "%s" is not a valid resolver. Resolvers should have a method "__invoke(...)"', $resolverName, $this->executableDefinition->getName());
        throw new \RuntimeException($error);
    }

    /**
     * @param HasExtensionsInterface $objectDefinition
     *
     * @return ExtensionInterface[]
     */
    private function resolveObjectExtensions(HasExtensionsInterface $objectDefinition): array
    {
        $extensions = [];

        //get all extensions registered as services
        $registeredExtensions = $this->container->get(ExtensionManager::class)->getExtensions();
        foreach ($registeredExtensions as $registeredExtension) {
            foreach ($objectDefinition->getExtensions() as $extensionDefinition) {
                $extensionClass = $extensionDefinition->getClass();
                if (\get_class($registeredExtension) === $extensionClass) {
                    $extensions[$extensionClass] = $registeredExtension;
                }
            }
        }

        //get all extensions not registered as services
        foreach ($objectDefinition->getExtensions() as $extensionDefinition) {
            $class = $extensionDefinition->getClass();
            if (!isset($extensions[$class])) {
                $instance = new $class();
                if ($instance instanceof ContainerAwareInterface) {
                    $instance->setContainer($this->container);
                }

                $extensions[$class] = $instance;
            }
        }

        return array_values($extensions);
    }

    /**
     * @param \ReflectionMethod $refMethod
     * @param array             $args
     * @param bool              $removeUnknownArguments
     *
     * @return array
     * @throws \Exception
     *
     */
    private function prepareMethodParameters(\ReflectionMethod $refMethod, array $args, $removeUnknownArguments = true): array
    {
        $normalizedArguments = $this->normalizeArguments($args);
        if (!$removeUnknownArguments) {
            $normalizedArguments = array_merge($args, $normalizedArguments);
        }
        $normalizedArguments['args'] = $normalizedArguments;
        $indexedArguments = $this->resolveMethodArguments($refMethod, $normalizedArguments);
        ksort($indexedArguments);

        return $indexedArguments;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    protected function normalizeArguments(array $args)
    {
        //normalize arguments
        $normalizedArguments = [];
        foreach ($args as $key => $value) {
            if ($this->executableDefinition->hasArgument($key)) {
                $argument = $this->executableDefinition->getArgument($key);
                if ('input' === $key) {
                    $normalizedValue = $value;
                } else {
                    $normalizedValue = $this->normalizeValue($value, $argument->getType());

                    //normalize argument into respective inputs objects
                    if (\is_array($normalizedValue)
                        && $this->endpoint->hasType($argument->getType())
                        && ($type = $this->endpoint->getType($argument->getType()))
                        && $type instanceof FieldsAwareDefinitionInterface
                    ) {
                        if ($argument->isList()) {
                            $tmp = [];
                            foreach ($normalizedValue as $childValue) {
                                $tmp[] = $this->arrayToObject($childValue, $this->endpoint->getType($argument->getType()));
                            }
                            $normalizedValue = $tmp;
                        } else {
                            $normalizedValue = $this->arrayToObject($normalizedValue, $this->endpoint->getType($argument->getType()));
                        }
                    }
                }
                $normalizedArguments[$argument->getName()] = $normalizedValue;
                $normalizedArguments[$argument->getInternalName()] = $normalizedValue;
            }
        }

        return $normalizedArguments;
    }

    /**
     * @param \ReflectionMethod $method
     * @param array             $incomeArgs
     *
     * @return array
     */
    private function resolveMethodArguments(\ReflectionMethod $method, array $incomeArgs)
    {
        $orderedArguments = [];
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->isOptional()) {
                $orderedArguments[$parameter->getPosition()] = $parameter->getDefaultValue();
            }
            foreach ($incomeArgs as $key => $value) {
                if ($parameter->getName() === $key) {
                    $orderedArguments[$parameter->getPosition()] = $value;
                    continue 2;
                }
            }

            //inject root common argument
            if ($this->root && !isset($incomeArgs['root']) && 'root' === $parameter->getName()) {
                $orderedArguments[$parameter->getPosition()] = $this->root;
            }

            //inject context common argument
            if ($this->context
                && 'context' === $parameter->getName()
                && ($type = $parameter->getType())
                && is_a($type->getName(), ResolverContext::class, true)
            ) {
                $orderedArguments[$parameter->getPosition()] = $this->context;
            }
        }

        // verify required arguments and types
        foreach ($method->getParameters() as $index => $parameter) {
            $givenValue = $orderedArguments[$index] ?? null;
            // does not allow null on required arguments
            if (null === $givenValue && !$parameter->allowsNull()) {
                throw new BadRequestError();
            }
        }

        return $orderedArguments;
    }

    /**
     * @param mixed  $value
     * @param string $type
     *
     * @return mixed
     */
    private function normalizeValue($value, string $type)
    {
        if (Types::ID === $type && $value) {
            if (\is_array($value)) {
                $idsArray = [];
                foreach ($value as $id) {
                    if ($id) {
                        $idsArray[] = $this->decodeID($id);
                    }
                }
                $value = $idsArray;
            } else {
                $value = $this->decodeID($value);
            }
        }

        return $value;
    }

    /**
     * @param string $globalId
     *
     * @return NodeInterface
     */
    private function decodeID(string $globalId): NodeInterface
    {
        try {
            $node = IDEncoder::decode($globalId);
            if (!$node) {
                throw new EntityNotFoundException();
            }

            return $node;
        } catch (EntityNotFoundException $exception) {
            throw new NotFoundError(sprintf('The given Node "%s" does not exists.', $globalId));
        }
    }

    /**
     * Convert a array into object using given definition
     *
     * @param array                          $data       data to populate the object
     * @param FieldsAwareDefinitionInterface $definition object definition
     *
     * @return mixed
     */
    private function arrayToObject(array $data, FieldsAwareDefinitionInterface $definition)
    {
        $class = null;
        if ($definition instanceof ClassAwareDefinitionInterface) {
            $class = $definition->getClass();
        }

        //normalize data
        foreach ($data as $fieldName => &$value) {
            if (!$definition->hasField($fieldName)) {
                continue;
            }
            $fieldDefinition = $definition->getField($fieldName);
            $value = $this->normalizeValue($value, $fieldDefinition->getType());
        }
        unset($value);

        //instantiate object
        if (class_exists($class)) {
            $object = new $class();
        } else {
            $object = $data;
        }

        //populate object
        foreach ($data as $key => $value) {
            if (!$definition->hasField($key)) {
                continue;
            }
            $fieldDefinition = $definition->getField($key);

            if (\is_array($value) && $this->endpoint->hasType($fieldDefinition->getType())) {
                $childType = $this->endpoint->getType($fieldDefinition->getType());
                if ($childType instanceof FieldsAwareDefinitionInterface) {
                    $value = $this->arrayToObject($value, $childType);
                }
            }

            $this->setObjectValue($object, $fieldDefinition, $value);
        }

        return $object;
    }

    /**
     * @param mixed           $object
     * @param FieldDefinition $fieldDefinition
     * @param mixed           $value
     *
     * @throws \ReflectionException
     */
    private function setObjectValue(&$object, FieldDefinition $fieldDefinition, $value): void
    {
        //using setter
        $accessor = new PropertyAccessor();
        $propertyName = $fieldDefinition->getOriginName();
        if (\is_array($object)) {
            $object[$propertyName ?? $fieldDefinition->getName()] = $value;
        } else {
            if ($accessor->isWritable($object, $propertyName)) {
                $accessor->setValue($object, $propertyName, $value);
            } else {
                //using reflection
                $refClass = new \ReflectionClass(\get_class($object));
                if ($refClass->hasProperty($object) && $property = $refClass->getProperty($object)) {
                    $property->setAccessible(true);
                    $property->setValue($object, $value);
                }
            }
        }
    }
}
