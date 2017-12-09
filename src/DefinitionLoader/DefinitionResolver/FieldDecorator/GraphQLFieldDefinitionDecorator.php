<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver\FieldDecorator;

use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver\AnnotationReaderAwareTrait;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Class GraphQLFieldDefinitionDecorator
 */
class GraphQLFieldDefinitionDecorator implements FieldDefinitionDecoratorInterface
{
    use AnnotationReaderAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function decorateFieldDefinition($field, FieldDefinition $definition, ObjectDefinitionInterface $objectDefinition)
    {
        if (!$field instanceof \ReflectionProperty && !$field instanceof \ReflectionMethod) {
            throw new \InvalidArgumentException('Invalid argument, expected reflection of property or method');
        }

        if (($name = $this->resolveFieldName($field)) && null !== $name) {
            $definition->setName($name);
        }

        if (($type = $this->resolveFieldType($field)) && null !== $type) {
            $definition->setType($this->resolveFieldType($field));
            $definition->setList($this->resolveFieldIsList($field));
            $definition->setNonNull($this->resolveFieldNonNull($field));
            $definition->setNonNullList($this->resolveFieldNonNullList($field));
        }

        if (($description = $this->resolveFieldDescription($field)) && null !== $description) {
            $definition->setDescription($description);
        }

        if (($deprecationReason = $this->resolveFieldDeprecationReason($field)) && null !== $deprecationReason) {
            $definition->setDeprecationReason($deprecationReason);
        }
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
     * @return bool|null
     */
    protected function resolveFieldNonNull($prop): ?bool
    {
        /** @var Annotation\Field $annotationField */
        $annotationField = $this->getFieldAnnotation($prop, Annotation\Field::class);
        if ($annotationField && $annotationField->type) {
            return TypeUtil::isTypeNonNull($annotationField->type);
        }

        return null;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return bool|null
     */
    protected function resolveFieldNonNullList($prop): ?bool
    {
        /** @var Annotation\Field $annotationField */
        $annotationField = $this->getFieldAnnotation($prop, Annotation\Field::class);
        if ($annotationField && $annotationField->type) {
            return TypeUtil::isTypeNonNullList($annotationField->type);
        }

        return null;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return bool|null
     */
    protected function resolveFieldIsList($prop): ?bool
    {
        /** @var Annotation\Field $annotationField */
        $annotationField = $this->getFieldAnnotation($prop, Annotation\Field::class);
        if ($annotationField && $annotationField->type) {
            return TypeUtil::isTypeList($annotationField->type);
        }

        return null;
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
        if ($annotationField && $annotationField->type) {
            $type = TypeUtil::normalize($annotationField->type);
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
            return lcfirst(preg_replace('/^(get|set)/', null, $prop->name));
        }

        return $prop->name;
    }
}
