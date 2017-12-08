<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition;

use Doctrine\Common\Util\ClassUtils;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Ynlo\GraphQLBundle\Action\APIActionInterface;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;
use Ynlo\GraphQLBundle\Model\ID;

/**
 * Class ResolverExecutor
 */
class ResolverExecutor implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var QueryDefinition
     */
    protected $query;

    /**
     * @var DefinitionManager
     */
    protected $manager;

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

    /**
     * @param ContainerInterface $container
     * @param DefinitionManager  $manager
     * @param QueryDefinition    $query
     */
    public function __construct(ContainerInterface $container, DefinitionManager $manager, QueryDefinition $query)
    {
        $this->query = $query;
        $this->manager = $manager;
        $this->container = $container;
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

        $resolverName = $this->query->getResolver();

        $resolver = null;
        $refMethod = null;

        if (class_exists($resolverName)) {
            $refClass = new \ReflectionClass($resolverName);

            /** @var callable $resolver */
            $resolver = $refClass->newInstance();
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
            $resolveContext->setDefinition($this->query);
            $resolveContext->setArgs($args);
            $resolveContext->setRoot($root);
            $resolveContext->setDefinitionManager($this->manager);
            $resolveContext->setResolveInfo($resolveInfo);

            if ($resolver instanceof APIActionInterface) {
                $resolver->setContext($resolveContext);
            }

            //A very strange issue are causing the fail of some tests without this clear
            //everything indicates that is a issue with cached entities through test executions
            //any clear on tests or during load fixtures does not have any effect
            //I'm not sure if this patch has any other side effect
            //Reproduce: comment the above line and run all integration tests
            //FIXME: find the cause of the issue and fix it
            $this->container->get('doctrine')->getManager()->clear();
            $params = $this->prepareMethodParameters($refMethod, $args);

            return $refMethod->invokeArgs($resolver, $params);
        }

        $error = sprintf('The resolver "%s" for query "%s" is not a valid resolver. Resolvers should have a method "__invoke(...)"', $resolverName, $this->query->getName());
        throw new \RuntimeException($error);
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
            if ($this->query->hasArgument($key)) {
                $argument = $this->query->getArgument($key);
                if ('input' === $key) {
                    $normalizedValue = $value;
                } else {
                    $normalizedValue = $this->normalizeValue($value, $argument->getType());

                    //normalize argument into respective inputs objects
                    if (is_array($normalizedValue) && $this->manager->hasType($argument->getType())) {
                        if ($argument->isList()) {
                            $tmp = [];
                            foreach ($normalizedValue as $childValue) {
                                $tmp[] = $this->arrayToObject($childValue, $this->manager->getType($argument->getType()));
                            }
                            $normalizedValue = $tmp;
                        } else {
                            $normalizedValue = $this->arrayToObject($normalizedValue, $this->manager->getType($argument->getType()));
                        }
                    }
                }
                $normalizedArguments[$argument->getName()] = $normalizedValue;
                $normalizedArguments[$argument->getInternalName()] = $normalizedValue;
            }
        }

        //parameters inside input will be injected as is
        //allowing the use of any of this parameters out of input
        //e.g. [input][id] => ($id)
        //        $inputType = null;
        //        if (isset($args['input']) && $this->query->hasArgument('input')) {
        //            $inputType = $this->query->getArgument('input')->getType();
        //            foreach ($args['input'] as $key => $value) {
        //                $fieldDefinition = $this->manager->getType($inputType)->getField($key);
        //                $normalizedValue = $this->normalizeValue($value, $fieldDefinition->getType());
        //                $normalizedArguments[$key] = $normalizedValue;
        //            }
        //        }

        //   $this->applyConventions($normalizedArguments);

        //if node exist, apply all arguments to populate the object
        //        if (isset($normalizedArguments['node'], $normalizedArguments['id'], $normalizedArguments['input'])
        //            && is_object($normalizedArguments['node'])
        //            && $normalizedArguments['id'] instanceof ID
        //        ) {
        //            /** @var ID $id */
        //            $id = $normalizedArguments['id'];
        //            $id->getNodeType();
        //            if ($this->manager->hasType($id->getNodeType())) {
        //                $this->arrayToObject(
        //                    $normalizedArguments['input'],
        //                    $this->manager->getType($id->getNodeType()),
        //                    $normalizedArguments['node']
        //                );
        //            }
        //        }

        $indexedArguments = $this->resolveMethodArguments($refMethod, $normalizedArguments);
        ksort($indexedArguments);

        return $indexedArguments;
    }

    /**
     * Apply conventions to a set of arguments
     * e.g.
     *
     * - If arguments contains a key 'id' with type ID,
     * a new key 'node' will be created with the real object
     *  > $id(ID) => $node(Object)
     *
     * - foreach arguments containing a key with suffix '*id' with type ID
     * a key with the same name (without '*id') will be created with the real object
     * > $userId(ID) => $user(Object)
     *
     * - If exist a input argument, the query has a valid type and the node has not been created yet
     *   create the node using the query type and input data
     * > $input(array) => $node(object)
     *
     * @param array $arguments
     */
    protected function applyConventions(array &$arguments)
    {
        if (isset($arguments['id']) && $arguments['id'] instanceof ID && !isset($arguments['node'])) {
            $arguments['node'] = $this->resolveIdObject($arguments['id']);
        }

        foreach ($arguments as $key => $value) {
            if (preg_match('/(\w+)Id$/', $key, $matches) && !isset($arguments[$matches[1]]) && $arguments[$key] instanceof ID) {
                $arguments[$matches[1]] = $this->resolveIdObject($value);
            }
        }

        if (!isset($arguments['node']) && isset($arguments['input']) && $this->manager->hasType($this->query->getType())) {
            $objectDefinition = $this->manager->getType($this->query->getType());
            $node = $this->arrayToObject($arguments['input'], $objectDefinition);
            if (\is_object($node)) {
                $arguments['node'] = $node;
            }
        }
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
        if (Type::ID === $type) {
            if (\is_array($value)) {
                $idsArray = [];
                foreach ($value as $id) {
                    $idsArray[] = ID::createFromString($id);
                }
                $value = $idsArray;
            } else {
                $value = ID::createFromString($value);
            }
        }

        return $value;
    }

    /**
     * @param ID $ID
     *
     * @return null|object
     */
    protected function resolveIdObject(ID $ID)
    {
        if ($this->manager->hasType($ID->getNodeType())) {
            $managedClass = $this->manager->getType($ID->getNodeType())->getClass();
            $objectManager = $this->container->get('doctrine')->getManagerForClass($managedClass);
            if (null !== $objectManager) {
                return $objectManager->find($managedClass, $ID->getDatabaseId());
            }
        }

        return null;
    }

    /**
     * Convert a array into object using given definition, if  the third parameter is given
     * this object will be populated instead of create new instance
     *
     * @param array                     $data       data to populate the object
     * @param ObjectDefinitionInterface $definition object definition
     * @param mixed                     $object     populate given object instead of create new one
     *
     * @return mixed
     */
    protected function arrayToObject(array $data, ObjectDefinitionInterface $definition, $object = null)
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
        if (null === $object) {
            if (!class_exists($class)) {
                return $data;
            }

            if (class_exists($class)) {
                $objectManager = $this->container->get('doctrine')->getManagerForClass($class);
                if (null !== $objectManager
                    && isset($data['id'])
                    && $data['id']
                    && $id = ID::createFromString($data['id'])->getDatabaseId()
                ) {
                    $object = $objectManager->find($class, $id);
                } else {
                    $object = new $class();
                }
            }
        }

        //populate object
        foreach ($data as $key => $value) {
            if (!$definition->hasField($key)) {
                continue;
            }
            $fieldDefinition = $definition->getField($key);

            if ($this->manager->hasType($fieldDefinition->getType())) {
                if ($fieldDefinition->isList()) {
                    $valueArray = [];
                    foreach ($value as $item) {
                        if (\is_array($item)) {
                            $valueArray[] = $this->arrayToObject(
                                $item,
                                $this->manager->getType($fieldDefinition->getType())
                            );
                        }
                    }
                    $value = $valueArray;
                } else {
                    $childObject = null;
                    if (($fieldValue = $this->getObjectValue($object, $fieldDefinition)) && \is_object($fieldValue)) {
                        $childObject = $fieldValue;
                    }
                    if (\is_array($value)) {
                        $value = $this->arrayToObject(
                            $value,
                            $this->manager->getType($fieldDefinition->getType()),
                            $childObject
                        );
                    }
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
     */
    protected function setObjectValue($object, FieldDefinition $fieldDefinition, $value)
    {
        if ($value instanceof ID) {
            $value = $this->resolveIdObject($value);
            if (!$value) {
                throw new UserError(sprintf('Invalid ID given in "%s"', $fieldDefinition->getName()));
            }
        }

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

    /**
     * @param mixed           $object
     * @param FieldDefinition $fieldDefinition
     *
     * @return mixed|null
     */
    protected function getObjectValue($object, FieldDefinition $fieldDefinition)
    {
        //using setter
        $accessor = new PropertyAccessor();
        $propertyName = $fieldDefinition->getOriginName();
        if ($propertyName) {
            if ($accessor->isReadable($object, $propertyName)) {
                return $accessor->getValue($object, $propertyName);
            }

            //using reflection
            $refClass = new \ReflectionClass(\get_class($object));
            if ($refClass->hasProperty($fieldDefinition->getOriginName()) && $property = $refClass->getProperty($fieldDefinition->getOriginName())) {
                $property->setAccessible(true);

                return $property->getValue($object);
            }
        }

        return null;
    }
}
