<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation\FieldDecorator;

use Doctrine\Common\Annotations\Reader;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Decorate a field definition using common GraphQL annotations
 */
class GraphQLFieldDefinitionDecorator implements FieldDefinitionDecoratorInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * GraphQLFieldDefinitionDecorator constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function decorateFieldDefinition($field, FieldDefinition $definition, ObjectDefinitionInterface $objectDefinition)
    {
        if (!$field instanceof \ReflectionProperty && !$field instanceof \ReflectionMethod) {
            throw new \InvalidArgumentException('Invalid argument, expected reflection of property or method');
        }

        if (null !== $name = $this->resolveFieldName($field)) {
            $definition->setName($name);
        }

        if (null !== $type = $this->resolveFieldType($field)) {
            $definition->setType($this->resolveFieldType($field));
            $definition->setList($this->resolveFieldIsList($field));
            $definition->setNonNull($this->resolveFieldNonNull($field));
            $definition->setNonNullList($this->resolveFieldNonNullList($field));
        }

        if (null !== $description = $this->resolveFieldDescription($field)) {
            $definition->setDescription($description);
        }

        if ($metas = $this->resolveFieldMetas($field)) {
            foreach ($metas as $metaName => $metaConfig) {
                if ($metaConfig instanceof Annotation\Plugin\PluginConfigAnnotation) {
                    $metaName = $metaConfig->getName();
                    $metaConfig = $metaConfig->getConfig();
                }
                $existentConfig = $definition->getMeta($metaName, []);
                if (\is_array($existentConfig)) {
                    $metaConfig = array_merge($existentConfig, $metaConfig);
                }
                $definition->setMeta($metaName, $metaConfig);
            }
        }

        if (null !== $deprecationReason = $this->resolveFieldDeprecationReason($field)) {
            $definition->setDeprecationReason($deprecationReason);
        }

        if (null !== $complexity = $this->resolveFieldComplexity($field)) {
            $definition->setComplexity($complexity);
        }

        if ($maxConcurrentUsage = $this->resolveFieldMaxConcurrentUsage($field)) {
            $definition->setMaxConcurrentUsage($maxConcurrentUsage);
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
     * @return array|null
     */
    protected function resolveFieldMetas($prop): ?array
    {
        /** @var Annotation\Field $annotation */
        if ($annotation = $this->getFieldAnnotation($prop, Annotation\Field::class)) {
            return $annotation->options;
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
    protected function resolveFieldComplexity($prop): ?string
    {
        /** @var Annotation\Field $annotation */
        if ($annotation = $this->getFieldAnnotation($prop, Annotation\Field::class)) {
            return $annotation->complexity;
        }

        return null;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $prop
     *
     * @return string|null
     */
    protected function resolveFieldMaxConcurrentUsage($prop): ?string
    {
        /** @var Annotation\Field $annotation */
        if ($annotation = $this->getFieldAnnotation($prop, Annotation\Field::class)) {
            return $annotation->maxConcurrentUsage;
        }

        return 0;
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
            if ($methodName = lcfirst(preg_replace('/^(get|set|has|is)/', null, $prop->name))) {
                return $methodName;
            }
        }

        return $prop->name;
    }
}
