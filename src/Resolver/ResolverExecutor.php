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
use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Ynlo\GraphQLBundle\Component\AutoWire\AutoWire;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\HasExtensionsInterface;
use Ynlo\GraphQLBundle\Definition\NodeAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Events\EventDispatcherAwareInterface;
use Ynlo\GraphQLBundle\Extension\ExtensionInterface;
use Ynlo\GraphQLBundle\Extension\ExtensionManager;
use Ynlo\GraphQLBundle\Extension\ExtensionsAwareInterface;
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
     * @var array
     */
    protected $args = [];

    public function __construct(ContainerInterface $container, Endpoint $endpoint, ExecutableDefinitionInterface $executableDefinition)
    {
        $this->container = $container;
        $this->endpoint = $endpoint;
        $this->executableDefinition = $executableDefinition;
    }

    /**
     * @param mixed       $root
     * @param array       $args
     * @param mixed       $context
     * @param ResolveInfo $resolveInfo
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __invoke($root, array $args, $context, ResolveInfo $resolveInfo)
    {
        $this->root = $root;
        $this->args = $args;
        $this->context = $context;
        $this->resolveInfo = $resolveInfo;

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

        if ($resolver && $refMethod) {
            $resolveContext = new ResolverContext();
            $resolveContext->setDefinition($this->executableDefinition);
            $resolveContext->setArgs($args);
            $resolveContext->setRoot($root);
            $resolveContext->setEndpoint($this->endpoint);
            $resolveContext->setResolveInfo($resolveInfo);

            $type = null;
            if ($this->executableDefinition instanceof NodeAwareDefinitionInterface && $this->executableDefinition->getNode()) {
                $type = $this->executableDefinition->getNode();
            }

            if (!$type && $this->executableDefinition->hasMeta('node')) {
                $type = $this->executableDefinition->getMeta('node');
            }
            if (!$type) {
                $type = $this->executableDefinition->getType();
            }

            $nodeDefinition = null;
            if ($this->endpoint->hasType($type)) {
                if ($nodeDefinition = $this->endpoint->getType($type)) {
                    $resolveContext->setNodeDefinition($nodeDefinition);
                }
            }

            if ($resolver instanceof ResolverInterface) {
                $resolver->setContext($resolveContext);
            }

            if ($resolver instanceof ExtensionsAwareInterface && $nodeDefinition instanceof HasExtensionsInterface) {
                $resolver->setExtensions($this->resolveObjectExtensions($nodeDefinition));
            }

            if ($resolver instanceof EventDispatcherAwareInterface) {
                $resolver->setEventDispatcher($this->container->get(EventDispatcherInterface::class));
            }

            $params = $this->prepareMethodParameters($refMethod, $args);

            return $refMethod->invokeArgs($resolver, $params);
        }

        $error = sprintf('The resolver "%s" for executableDefinition "%s" is not a valid resolver. Resolvers should have a method "__invoke(...)"', $resolverName, $this->executableDefinition->getName());
        throw new \RuntimeException($error);
    }

    /**
     * @param HasExtensionsInterface $objectDefinition
     *
     * @return ExtensionInterface[]
     */
    protected function resolveObjectExtensions(HasExtensionsInterface $objectDefinition): array
    {
        $extensions = [];

        //get all extensions registered as services
        $registeredExtensions = $this->container->get(ExtensionManager::class)->getExtensions();
        foreach ($registeredExtensions as $registeredExtension) {
            foreach ($objectDefinition->getExtensions() as $extensionDefinition) {
                $extensionClass = $extensionDefinition->getClass();
                if (get_class($registeredExtension) === $extensionClass) {
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
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function prepareMethodParameters(\ReflectionMethod $refMethod, array $args): array
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
                    if (is_array($normalizedValue) && $this->endpoint->hasType($argument->getType())) {
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
        $normalizedArguments['args'] = $normalizedArguments;
        $normalizedArguments['root'] = $this->root;
        $indexedArguments = $this->resolveMethodArguments($refMethod, $normalizedArguments);
        ksort($indexedArguments);

        return $indexedArguments;
    }

    /**
     * @param \ReflectionMethod $method
     * @param array             $incomeArgs
     *
     * @return array
     */
    protected function resolveMethodArguments(\ReflectionMethod $method, array $incomeArgs)
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
            if ($this->root
                && 'root' === $parameter->getName()
                && $parameter->getClass()
                && $parameter->getClass()->isInstance($this->root)

            ) {
                $orderedArguments[$parameter->getPosition()] = $this->root;
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
    protected function normalizeValue($value, string $type)
    {
        if (Types::ID === $type && $value) {
            if (\is_array($value)) {
                $idsArray = [];
                foreach ($value as $id) {
                    if ($id) {
                        $idsArray[] = IDEncoder::decode($id);
                    }
                }
                $value = $idsArray;
            } else {
                $value = IDEncoder::decode($value);
            }
        }

        return $value;
    }

    /**
     * Convert a array into object using given definition
     *
     * @param array                     $data       data to populate the object
     * @param ObjectDefinitionInterface $definition object definition
     *
     * @return mixed
     */
    protected function arrayToObject(array $data, ObjectDefinitionInterface $definition)
    {
        $class = $definition->getClass();

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
            return $data;
        }

        //populate object
        foreach ($data as $key => $value) {
            if (!$definition->hasField($key)) {
                continue;
            }
            $fieldDefinition = $definition->getField($key);
            $this->setObjectValue($object, $fieldDefinition, $value);
        }

        return $object;
    }

    /**
     * @param mixed           $object
     * @param FieldDefinition $fieldDefinition
     * @param mixed           $value
     */
    protected function setObjectValue($object, FieldDefinition $fieldDefinition, $value)
    {
        //using setter
        $accessor = new PropertyAccessor();
        $propertyName = $fieldDefinition->getOriginName();
        if ($propertyName) {
            if ($accessor->isWritable($object, $propertyName)) {
                $accessor->setValue($object, $propertyName, $value);
            } else {
                //using reflection
                $refClass = new \ReflectionClass(\get_class($object));
                if ($refClass->hasProperty($fieldDefinition->getOriginName()) && $property = $refClass->getProperty($fieldDefinition->getOriginName())) {
                    $property->setAccessible(true);
                    $property->setValue($object, $value);
                }
            }
        }
    }
}
