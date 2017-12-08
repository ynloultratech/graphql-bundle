<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver;

use Symfony\Component\DependencyInjection\Definition;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServices;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver\FieldDecorator\FieldDefinitionDecoratorInterface;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver\FieldDecorator\FieldMetadata;
use Ynlo\GraphQLBundle\Type\DefinitionManagerAwareInterface;
use Ynlo\GraphQLBundle\Type\TypeUtil;

/**
 * Resolve object types using ObjectType annotation
 */
class ObjectTypeResolver implements DefinitionResolverInterface
{
    use AnnotationReaderAwareTrait;

    /**
     * @var TaggedServices
     */
    protected $taggedServices;

    /**
     * @var DefinitionManager
     */
    protected $definitionManager;

    /**
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
    public function resolve($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        $this->definitionManager = $definitionManager;

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

        if ($definitionManager->hasType($objectDefinition->getName())) {
            return;
        }

        $objectDefinition->setClass($refClass->getName());
        $objectDefinition->setDescription($annotation->description);

        if ($objectDefinition instanceof ObjectDefinition) {
            $this->resolveObjectInterfaces($refClass, $objectDefinition, $definitionManager);
        }

        $this->loadInheritedProperties($refClass, $objectDefinition);
        $this->resolveFields($refClass, $objectDefinition);

        $definitionManager->addType($objectDefinition);
    }

    /**
     * @param \ReflectionClass  $refClass
     * @param ObjectDefinition  $objectDefinition
     * @param DefinitionManager $definitionManager
     */
    protected function resolveObjectInterfaces(\ReflectionClass $refClass, ObjectDefinition $objectDefinition, DefinitionManager $definitionManager)
    {
        $int = $refClass->getInterfaces();
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

                if (!$intDef->getName() && preg_match('/\w+$/', $intRef->getName(), $matches)) {
                    $intDef->setName(preg_replace('/Interface$/', null, $matches[0]));
                }

                $objectDefinition->addInterface($intDef->getName());

                if ($definitionManager->hasType($intDef->getName())) {
                    /** @var InterfaceDefinition $existentInterfaceDefinition */
                    $existentInterfaceDefinition = $definitionManager->getType($intDef->getName());
                    $existentInterfaceDefinition->addImplementor($objectDefinition->getName());
                    $this->copyFieldsFromInterface($existentInterfaceDefinition, $objectDefinition);
                    continue;
                }

                $intDef->setDescription($intAnnot->description);
                $intDef->addImplementor($objectDefinition->getName());

                $this->resolveFields($intRef, $intDef);
                $this->copyFieldsFromInterface($intDef, $objectDefinition);
                $definitionManager->addType($intDef);
            }
        }
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
     * @param InterfaceDefinition $intDef
     * @param ObjectDefinition    $objectDefinition
     */
    protected function copyFieldsFromInterface(InterfaceDefinition $intDef, ObjectDefinition $objectDefinition)
    {
        foreach ($intDef->getFields() as $field) {
            if (!$objectDefinition->hasField($field->getName())) {
                $objectDefinition->addField(clone $field);
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
                $field->setOriginName($prop->name);
                $field->setOriginType(\get_class($prop));

                foreach ($fieldDecorators as $fieldDecorator) {
                    $fieldDecorator->decorateFieldDefinition($prop, $field, $objectDefinition);
                }

                if ($objectDefinition->hasField($field->getName())) {
                    $field = $objectDefinition->getField($field->getName());
                } else {
                    $objectDefinition->addField($field);
                }

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
                    if ($annotation->hidden === true) {
                        $objectDefinition->removeField($annotation->name);
                        continue;
                    }
                    if ($annotation->description) {
                        $fieldDefinition->setDescription($annotation->description);
                    }
                    if ($annotation->deprecationReason || $annotation->deprecationReason === false) {
                        $fieldDefinition->setDeprecationReason($annotation->deprecationReason);
                    }
                    if ($annotation->type) {
                        $fieldDefinition->setType($annotation->type);
                    }
                    if ($annotation->alias) {
                        $fieldDefinition->setName($annotation->alias);
                    }
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

            if ($decorator instanceof DefinitionManagerAwareInterface) {
                $decorator->setDefinitionManager($this->definitionManager);
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

        return $exposed;
    }

    /**
     * Get field specific annotation matching given objectDefinition
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
