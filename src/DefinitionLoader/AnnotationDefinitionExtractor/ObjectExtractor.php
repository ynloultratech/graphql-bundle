<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\AnnotationDefinitionExtractor;

use GraphQL\Type\Definition\Type;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;

/**
 * Class ObjectExtractor
 */
class ObjectExtractor extends AbstractAnnotationDefinitionExtractor
{
    /**
     * {@inheritDoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\ObjectType || $annotation instanceof Annotation\InputObjectType;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        if ($annotation instanceof Annotation\InputObjectType) {
            $objectDefinition = new InputObjectDefinition();
        } else {
            $objectDefinition = new ObjectDefinition();
        }

        $objectDefinition->setName($annotation->name);

        if (!$objectDefinition->getName()) {
            preg_match('/\w+$/', $refClass->getName(), $matches);
            $objectDefinition->setName($matches[0] ?? '');
        }

        if ($definitionManager->hasType($objectDefinition->getName())) {
            return;
        }

        $objectDefinition->setClass($refClass->getName());
        $objectDefinition->setDescription($annotation->description);

        if ($annotation instanceof Annotation\ObjectType) {
            $this->loadObjectInterfaces($refClass, $objectDefinition, $definitionManager);
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
    protected function loadObjectInterfaces(\ReflectionClass $refClass, ObjectDefinition $objectDefinition, DefinitionManager $definitionManager)
    {
        $interfaces = $refClass->getInterfaces();
        foreach ($interfaces as $interfaceReflection) {

            /** @var Annotation\InterfaceType $interfaceAnnotation */
            $interfaceAnnotation = $this->reader->getClassAnnotation(
                $interfaceReflection,
                Annotation\InterfaceType::class
            );

            if ($interfaceAnnotation) {
                $interfaceDefinition = new InterfaceDefinition();
                $interfaceDefinition->setName($interfaceAnnotation->name);

                if (!$interfaceDefinition->getName() && preg_match('/\w+$/', $interfaceReflection->getName(), $matches)) {
                    $interfaceDefinition->setName(preg_replace('/Interface$/', null, $matches[0]));
                }

                $objectDefinition->addInterface($interfaceDefinition->getName());

                if ($definitionManager->hasType($interfaceDefinition->getName())) {
                    /** @var InterfaceDefinition $existentInterfaceDefinition */
                    $existentInterfaceDefinition = $definitionManager->getType($interfaceDefinition->getName());
                    $existentInterfaceDefinition->addImplementor($objectDefinition->getName());
                    $this->copyFieldsFromInterface($existentInterfaceDefinition, $objectDefinition);
                    continue;
                }

                $interfaceDefinition->setDescription($interfaceAnnotation->description);
                $interfaceDefinition->addImplementor($objectDefinition->getName());

                $this->resolveFields($interfaceReflection, $interfaceDefinition);
                $this->copyFieldsFromInterface($interfaceDefinition, $objectDefinition);
                $definitionManager->addType($interfaceDefinition);
            }
        }
    }

    /**
     * @param \ReflectionClass $refClass
     * @param ObjectDefinition $objectDefinition
     */
    protected function loadInheritedProperties(\ReflectionClass $refClass, ObjectDefinition $objectDefinition)
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
                $objectDefinition->addField($field);
            }
        }
    }

    /**
     * Extract all fields for given definition
     *
     * @param \ReflectionClass               $refClass
     * @param FieldsAwareDefinitionInterface $fieldsAwareDefinition
     */
    protected function resolveFields(\ReflectionClass $refClass, FieldsAwareDefinitionInterface $fieldsAwareDefinition)
    {
        $props = array_merge($this->getClassProperties($refClass), $this->getClassMethods($refClass));

        foreach ($props as $prop) {
            if ($this->isExposed($fieldsAwareDefinition, $prop)) {
                $field = new FieldDefinition();
                $field->setName($this->resolveFieldName($prop));
                $field->setNonNull($this->resolveFieldNonNull($prop) ?? false);
                $field->setNonNullList($this->resolveFieldNonNullList($prop) ?? false);
                $field->setReadOnly($this->resolveFieldReadOnly($fieldsAwareDefinition, $prop) ?? false);
                $field->setDescription($this->resolveFieldDescription($prop) ?? null);
                $field->setDeprecationReason($this->resolveFieldDeprecationReason($prop) ?? null);
                $field->setInputRelation($this->resolveFieldInputRelation($prop) ?? null);
                $field->setList($this->resolveFieldIsList($prop) ?? null);
                $field->setType($this->resolveFieldType($prop) ?? null);

                if (!$field->getType()) {
                    throw new \RuntimeException(
                        sprintf(
                            'Must define a valid field type for %s:%s',
                            $fieldsAwareDefinition->getName(),
                            $field->getName()
                        )
                    );
                }

                $field->setOriginName($prop->name);
                $field->setOriginType(\get_class($prop));
                $fieldsAwareDefinition->addField($field);
            }
        }

        //load overrides
        $annotations = $this->reader->getClassAnnotations($refClass);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\OverrideField) {
                if ($fieldsAwareDefinition->hasField($annotation->name)) {
                    $fieldDefinition = $fieldsAwareDefinition->getField($annotation->name);
                    if ($annotation->hidden === true) {
                        $fieldsAwareDefinition->removeField($annotation->name);
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
                    if ($annotation->readOnly !== null) {
                        $fieldDefinition->setReadOnly($annotation->readOnly);
                    }
                    if ($annotation->alias) {
                        $fieldDefinition->setName($annotation->alias);
                    }
                }
            }
        }
    }

    /**
     * Verify if a given property for given definition is exposed or not
     *
     * @param DefinitionInterface                   $definition
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return boolean
     */
    protected function isExposed(DefinitionInterface $definition, $prop): bool
    {
        if (!$definition instanceof ObjectDefinition
            && !$definition instanceof InputObjectDefinition
            && !$definition instanceof InterfaceDefinition
        ) {
            return true;
        }

        return (bool) $this->getFieldAnnotation($prop, Annotation\Field::class);
    }

    /**
     * Get field specific annotation matching given fieldsAwareDefinition
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
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return int|null
     */
    protected function resolveFieldInputRelation($prop): ?int
    {
        if ($annotation = $this->getFieldAnnotation($prop, Annotation\InputById::class)) {
            return FieldDefinition::INPUT_BY_ID;
        }

        if ($annotation = $this->getFieldAnnotation($prop, Annotation\InputInline::class)) {
            return FieldDefinition::INPUT_INLINE;
        }

        return null;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return string|null
     */
    protected function resolveFieldDescription($prop): ?string
    {
        /** @var Annotation\Field $annotation */
        if ($annotation = $this->getFieldAnnotation($prop, Annotation\Field::class)) {
            return $annotation->description;
        }

        return null;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return string|null
     */
    protected function resolveFieldDeprecationReason($prop): ?string
    {
        /** @var Annotation\Field $annotation */
        if ($annotation = $this->getFieldAnnotation($prop, Annotation\Field::class)) {
            return $annotation->deprecationReason;
        }

        return null;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return string|null
     */
    protected function resolveFieldNonNull($prop): ?string
    {
        /** @var Annotation\Field $annotationField */
        $annotationField = $this->getFieldAnnotation($prop, Annotation\Field::class);
        if ($annotationField) {
            return $this->isTypeNonNull($annotationField->type);
        }

        return false;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return string|null
     */
    protected function resolveFieldNonNullList($prop): ?string
    {
        /** @var Annotation\Field $annotationField */
        $annotationField = $this->getFieldAnnotation($prop, Annotation\Field::class);
        if ($annotationField) {
            return $this->isTypeNonNullList($annotationField->type);
        }

        return false;
    }

    /**
     * @param FieldsAwareDefinitionInterface        $definition
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return string|null
     */
    protected function resolveFieldReadOnly(FieldsAwareDefinitionInterface $definition, $prop): ?string
    {
        if ($prop instanceof \ReflectionMethod && !$definition instanceof InterfaceDefinition) {
            return true;
        }

        $annotationField = $this->getFieldAnnotation($prop, Annotation\Field::class);
        if ($annotationField && Type::ID === $this->getNormalizedType($annotationField->type)) {
            return true;
        }

        /** @var Annotation\Field $annotationField */
        $annotationField = $this->getFieldAnnotation($prop, Annotation\Field::class);
        if ($annotationField) {
            return $annotationField->readOnly;
        }

        return false;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return bool
     */
    protected function resolveFieldIsList($prop): bool
    {
        /** @var Annotation\Field $annotationField */
        $annotationField = $this->getFieldAnnotation($prop, Annotation\Field::class);
        if ($annotationField) {
            return $this->isTypeList($annotationField->type);
        }

        return false;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return string|null
     */
    protected function resolveFieldType($prop): ?string
    {
        $type = null;

        /** @var Annotation\Field $annotationField */
        $annotationField = $this->getFieldAnnotation($prop, Annotation\Field::class);
        if ($annotationField) {
            $type = $this->getNormalizedType($annotationField->type);
        }

        return $type;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function isTypeList($type): bool
    {
        return (bool) preg_match('/^\[(\w+)!?\]!?$/', $type);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function isTypeNonNullList($type): bool
    {
        return (bool) preg_match('/^\[(\w+)!\]!?$/', $type);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function isTypeNonNull($type): bool
    {
        return (bool) preg_match('/!$/', $type);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getNormalizedType($type)
    {
        if (preg_match('/^\[?(\w+)!?\]?!?$/', $type, $matches)) {
            $type = $matches[1];
        }

        switch ($type) {
            case 'bool':
            case 'boolean':
                $type = Type::BOOLEAN;
                break;
            case 'decimal':
            case 'float':
                $type = Type::FLOAT;
                break;
            case 'int':
            case 'integer':
                $type = Type::INT;
                break;
            case 'string':
                $type = Type::STRING;
                break;
            case 'id':
                $type = Type::ID;
                break;
            case 'datetime':
            case 'date_time':
            case 'date':
                $type = 'DateTime';
                break;
        }

        return $type;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return string|null
     */
    protected function resolveFieldName($prop): ?string
    {
        /** @var Annotation\Field $annotation */
        if (($annotation = $this->getFieldAnnotation($prop, Annotation\Field::class)) && $annotation->name) {
            return $annotation->name;
        }

        if ($prop instanceof \ReflectionMethod) {
            return lcfirst(preg_replace('/^(get|has|is)/', null, $prop->name));
        }

        return $prop->name;
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
