<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation;

use Symfony\Component\DependencyInjection\Definition;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServices;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ImplementorInterface;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\FieldDecorator\FieldDefinitionDecoratorInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Resolver\FieldExpressionResolver;
use Ynlo\GraphQLBundle\Type\Definition\EndpointAwareInterface;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Parse the ObjectType annotation to fetch object definitions
 */
class ObjectTypeAnnotationParser implements AnnotationParserInterface
{
    use AnnotationReaderAwareTrait;

    /**
     * @var TaggedServices
     */
    protected $taggedServices;
    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * ObjectTypeAnnotationParser constructor.
     *
     * @param TaggedServices $taggedServices
     */
    public function __construct(TaggedServices $taggedServices)
    {
        $this->taggedServices = $taggedServices;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\ObjectType || $annotation instanceof Annotation\InputObjectType;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;

        if ($annotation instanceof Annotation\ObjectType) {
            $objectDefinition = new ObjectDefinition();
        } else {
            $objectDefinition = new InputObjectDefinition();
        }

        $objectDefinition->setName($annotation->name);
        $objectDefinition->setExclusionPolicy($annotation->exclusionPolicy);
        $objectDefinition->setClass($refClass->name);

        if (!$objectDefinition->getName()) {
            preg_match('/\w+$/', $refClass->getName(), $matches);
            $objectDefinition->setName($matches[0] ?? '');
        }

        if ($endpoint->hasType($objectDefinition->getName())) {
            return;
        }

        $objectDefinition->setClass($refClass->getName());
        $objectDefinition->setDescription($annotation->description);

        if ($objectDefinition instanceof ImplementorInterface) {
            $this->resolveDefinitionInterfaces($refClass, $objectDefinition, $endpoint);
        }

        $this->loadInheritedProperties($refClass, $objectDefinition);
        $this->resolveFields($refClass, $objectDefinition);
        $endpoint->addType($objectDefinition);
    }

    /**
     * @param \ReflectionClass     $refClass
     * @param ImplementorInterface $implementor
     * @param Endpoint             $endpoint
     */
    protected function resolveDefinitionInterfaces(\ReflectionClass $refClass, ImplementorInterface $implementor, Endpoint $endpoint)
    {
        $interfaceDefinitions = $this->extractInterfaceDefinitions($refClass);
        foreach ($interfaceDefinitions as $interfaceDefinition) {
            $implementor->addInterface($interfaceDefinition->getName());

            if (!$endpoint->hasType($interfaceDefinition->getName())) {
                $endpoint->addType($interfaceDefinition);
            } else {
                $interfaceDefinition = $endpoint->getType($interfaceDefinition->getName());
            }

            $interfaceDefinition->addImplementor($implementor->getName());
            $this->copyFieldsFromInterface($interfaceDefinition, $implementor);
        }

        //support interface inheritance
        //Interface inheritance is implemented in GraphQL
        //@see https://github.com/facebook/graphql/issues/295
        //BUT, GraphQLBundle use this feature in some places like extensions etc.
        foreach ($interfaceDefinitions as $interfaceDefinition) {
            if ($interfaceDefinition->getClass()) {
                $childInterface = new \ReflectionClass($interfaceDefinition->getClass());
                $parentDefinitions = $this->extractInterfaceDefinitions($childInterface);
                foreach ($parentDefinitions as $parentDefinition) {
                    if ($endpoint->hasType($parentDefinition->getName())) {
                        $existentParentDefinition = $endpoint->getType($parentDefinition->getName());
                        if ($existentParentDefinition instanceof InterfaceDefinition) {
                            $existentParentDefinition->addImplementor($interfaceDefinition->getName());
                        }
                    }
                }
            }
        }
    }

    /**
     * @param \ReflectionClass $refClass
     *
     * @return InterfaceDefinition[]
     */
    protected function extractInterfaceDefinitions(\ReflectionClass $refClass)
    {
        $int = $refClass->getInterfaces();
        $definitions = [];
        foreach ($int as $intRef) {
            /** @var Annotation\InterfaceType $intAnnot */
            $intAnnot = $this->reader->getClassAnnotation(
                $intRef,
                Annotation\InterfaceType::class
            );

            if ($intAnnot) {
                $intDef = new InterfaceDefinition();
                $intDef->setName($intAnnot->name);
                $intDef->setClass($intRef->getName());
                $intDef->setDescription($intAnnot->description);
                $this->resolveFields($intRef, $intDef);
                if (!$intDef->getName() && preg_match('/\w+$/', $intRef->getName(), $matches)) {
                    $intDef->setName(preg_replace('/Interface$/', null, $matches[0]));
                }

                $definitions[] = $intDef;
            }
        }

        return $definitions;
    }

    /**
     * @param \ReflectionClass          $refClass
     * @param ObjectDefinitionInterface $objectDefinition
     */
    protected function loadInheritedProperties(\ReflectionClass $refClass, ObjectDefinitionInterface $objectDefinition)
    {
        while ($parent = $refClass->getParentClass()) {
            $this->resolveFields($refClass, $objectDefinition);
            $refClass = $parent;
        }
    }

    /**
     * Copy all fields from interface to given object implementor
     *
     * @param InterfaceDefinition            $intDef
     * @param FieldsAwareDefinitionInterface $fieldsAwareDefinition
     */
    protected function copyFieldsFromInterface(InterfaceDefinition $intDef, FieldsAwareDefinitionInterface $fieldsAwareDefinition)
    {
        foreach ($intDef->getFields() as $field) {
            if (!$fieldsAwareDefinition->hasField($field->getName())) {
                $newField = clone $field;
                $newField->addInheritedFrom($intDef->getName());
                $fieldsAwareDefinition->addField($newField);
            } else {
                $fieldsAwareDefinition->getField($field->getName())->addInheritedFrom($intDef->getName());
            }
        }
    }

    /**
     * Extract all fields for given definition
     *
     * @param \ReflectionClass          $refClass
     * @param ObjectDefinitionInterface $objectDefinition
     */
    protected function resolveFields(\ReflectionClass $refClass, ObjectDefinitionInterface $objectDefinition)
    {
        $props = array_merge($this->getClassProperties($refClass), $this->getClassMethods($refClass));

        $fieldDecorators = $this->getFieldDecorators();

        foreach ($props as $prop) {
            if ($this->isExposed($objectDefinition, $prop)) {
                $field = new FieldDefinition();
                foreach ($fieldDecorators as $fieldDecorator) {
                    $fieldDecorator->decorateFieldDefinition($prop, $field, $objectDefinition);
                }

                if ($objectDefinition->hasField($field->getName())) {
                    $field = $objectDefinition->getField($field->getName());
                } else {
                    $objectDefinition->addField($field);
                }

                $field->setOriginName($prop->name);
                $field->setOriginType(\get_class($prop));

                //resolve field arguments
                if ($prop instanceof \ReflectionMethod) {
                    $argAnnotations = $this->reader->getMethodAnnotations($prop);
                    foreach ($argAnnotations as $argAnnotation) {
                        if ($argAnnotation instanceof Annotation\Argument) {
                            $arg = new ArgumentDefinition();
                            $arg->setName($argAnnotation->name);
                            $arg->setDescription($argAnnotation->description);
                            $arg->setInternalName($argAnnotation->internalName);
                            $arg->setDefaultValue($argAnnotation->defaultValue);
                            $arg->setType(TypeUtil::normalize($argAnnotation->type));
                            $arg->setList(TypeUtil::isTypeList($argAnnotation->type));
                            $arg->setNonNullList(TypeUtil::isTypeNonNullList($argAnnotation->type));
                            $arg->setNonNull(TypeUtil::isTypeNonNull($argAnnotation->type));
                            $field->addArgument($arg);
                        }
                    }
                }
            }
        }

        //load overrides
        $annotations = $this->reader->getClassAnnotations($refClass);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\OverrideField) {
                if ($objectDefinition->hasField($annotation->name)) {
                    $fieldDefinition = $objectDefinition->getField($annotation->name);
                    if (true === $annotation->hidden) {
                        $objectDefinition->removeField($annotation->name);
                        continue;
                    }
                    if ($annotation->description) {
                        $fieldDefinition->setDescription($annotation->description);
                    }
                    if ($annotation->deprecationReason || false === $annotation->deprecationReason) {
                        $fieldDefinition->setDeprecationReason($annotation->deprecationReason);
                    }
                    if ($annotation->complexity) {
                        $fieldDefinition->setComplexity($annotation->complexity);
                    }
                    if ($annotation->type) {
                        $fieldDefinition->setType($annotation->type);
                    }
                    if ($annotation->alias) {
                        $fieldDefinition->setName($annotation->alias);
                        $objectDefinition->removeField($fieldDefinition->getName());
                        $objectDefinition->addField($fieldDefinition);
                    }
                } else {
                    $error = sprintf(
                        'The object definition "%s" does not have any field called "%s" in any of its parents definitions.',
                        $objectDefinition->getName(),
                        $annotation->name
                    );
                    throw new \InvalidArgumentException($error);
                }
            }
        }

        //load virtual fields
        $annotations = $this->reader->getClassAnnotations($refClass);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\VirtualField) {
                if (!$objectDefinition->hasField($annotation->name)) {
                    $fieldDefinition = new FieldDefinition();
                    $fieldDefinition->setName($annotation->name);
                    $fieldDefinition->setDescription($annotation->description);
                    $fieldDefinition->setDeprecationReason($annotation->deprecationReason);
                    $fieldDefinition->setType(TypeUtil::normalize($annotation->type));
                    $fieldDefinition->setNonNull(TypeUtil::isTypeNonNull($annotation->type));
                    $fieldDefinition->setNonNullList(TypeUtil::isTypeNonNullList($annotation->type));
                    $fieldDefinition->setList(TypeUtil::isTypeList($annotation->type));
                    $fieldDefinition->setMeta('expression', $annotation->expression);
                    $fieldDefinition->setResolver(FieldExpressionResolver::class);
                    $fieldDefinition->setComplexity($annotation->complexity);
                    $objectDefinition->addField($fieldDefinition);
                } else {
                    $fieldDefinition = $objectDefinition->getField($annotation->name);
                    if ($fieldDefinition->getResolver() === FieldExpressionResolver::class) {
                        continue;
                    }
                    $error = sprintf(
                        'The object definition "%s" already has a field called "%s".',
                        $objectDefinition->getName(),
                        $annotation->name
                    );
                    throw new \InvalidArgumentException($error);
                }
            }
        }
    }

    /**
     * @return array|FieldDefinitionDecoratorInterface[]
     */
    protected function getFieldDecorators(): array
    {
        /** @var Definition $resolversServiceDefinition */
        $decoratorsDef = $this->taggedServices
            ->findTaggedServices('graphql.field_definition_decorator');

        $decorators = [];
        foreach ($decoratorsDef as $decoratorDef) {
            $attr = $decoratorDef->getAttributes();
            $priority = 0;
            if (isset($attr['priority'])) {
                $priority = $attr['priority'];
            }

            $decorator = $decoratorDef->getService();

            if ($decorator instanceof EndpointAwareInterface) {
                $decorator->setEndpoint($this->endpoint);
            }

            $decorators[] = [$priority, $decorator];
        }

        //sort by priority
        usort(
            $decorators,
            function ($service1, $service2) {
                list($priority1) = $service1;
                list($priority2) = $service2;

                return version_compare($priority2, $priority1);
            }
        );

        return array_column($decorators, 1);
    }

    /**
     * Verify if a given property for given definition is exposed or not
     *
     * @param ObjectDefinitionInterface             $definition
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return boolean
     */
    protected function isExposed(ObjectDefinitionInterface $definition, $prop): bool
    {
        $exposed = $definition->getExclusionPolicy() === ObjectDefinitionInterface::EXCLUDE_NONE;

        if ($prop instanceof \ReflectionMethod) {
            $exposed = false;

            //implicit inclusion
            if ($this->getFieldAnnotation($prop, Annotation\Field::class)) {
                $exposed = true;
            }
        }

        if ($exposed && $this->getFieldAnnotation($prop, Annotation\Exclude::class)) {
            $exposed = false;
        } elseif (!$exposed && $this->getFieldAnnotation($prop, Annotation\Expose::class)) {
            $exposed = true;
        }

        if (!$exposed) {
            //verify if the field belong to a interface
            //in this case is always exposed
            $inheritedInterfaceFields = [];
            foreach ($definition->getFields() as $field) {
                if ($field->getInheritedFrom()) {
                    $inheritedInterfaceFields[] = lcfirst(preg_replace('/^(get|set|has|is)/', null, $field->getOriginName()));
                }
            }

            $exposed = \in_array($prop->name, $inheritedInterfaceFields, true);
        }

        return $exposed;
    }

    /**
     * Get field specific annotation matching given implementor
     *
     * @param \ReflectionMethod|\ReflectionProperty $prop
     * @param string                                $annotationClass
     *
     * @return mixed
     */
    protected function getFieldAnnotation($prop, string $annotationClass)
    {
        if ($prop instanceof \ReflectionProperty) {
            return $this->reader->getPropertyAnnotation($prop, $annotationClass);
        }

        return $this->reader->getMethodAnnotation($prop, $annotationClass);
    }

    /**
     * @param \ReflectionClass $refClass
     *
     * @return array
     */
    protected function getClassProperties(\ReflectionClass $refClass)
    {
        $props = [];
        foreach ($refClass->getProperties() as $prop) {
            $props[$prop->name] = $prop;
        }

        return $props;
    }

    /**
     * @param \ReflectionClass $refClass
     *
     * @return array
     */
    protected function getClassMethods(\ReflectionClass $refClass)
    {
        $methods = [];
        foreach ($refClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[$method->name] = $method;
        }

        return $methods;
    }
}
