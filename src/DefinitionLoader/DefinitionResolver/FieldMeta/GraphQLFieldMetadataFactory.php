<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver\FieldMeta;

use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver\AnnotationReaderAwareTrait;
use Ynlo\GraphQLBundle\Type\TypeUtil;

/**
 * Class GraphQLFieldMetadataFactory
 */
class GraphQLFieldMetadataFactory implements FieldMetadataFactoryInterface
{
    use AnnotationReaderAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getMetadataForField($field): FieldMetadata
    {
        if (!$field instanceof \ReflectionProperty && !$field instanceof \ReflectionMethod) {
            throw new \InvalidArgumentException('Invalid argument, expected reflection of property or method');
        }

        $fieldMeta = new FieldMetadata();
        $fieldMeta->name = $this->resolveFieldName($field);
        $fieldMeta->nonNull = $this->resolveFieldNonNull($field);
        $fieldMeta->nonNullList = $this->resolveFieldNonNullList($field);
        $fieldMeta->description = $this->resolveFieldDescription($field);
        $fieldMeta->deprecationReason = $this->resolveFieldDeprecationReason($field);
        $fieldMeta->list = $this->resolveFieldIsList($field);
        $fieldMeta->type = $this->resolveFieldType($field);

        return $fieldMeta;
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
        if ($annotationField) {
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
        if ($annotationField) {
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
        if ($annotationField) {
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
        if ($annotationField) {
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
            return lcfirst(preg_replace('/^(get|has|is|set)/', null, $prop->name));
        }

        return $prop->name;
    }
}