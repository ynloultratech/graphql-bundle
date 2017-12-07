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

use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use GraphQL\Type\Definition\Type;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver\AnnotationReaderAwareTrait;

/**
 * DoctrineFieldMetadataFactory
 */
class DoctrineFieldMetadataFactory implements FieldMetadataFactoryInterface
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

        if ($field instanceof \ReflectionMethod) {
            return $fieldMeta;
        }

        /** @var Column $column */
        if ($column = $this->reader->getPropertyAnnotation($field, Column::class)) {
            $fieldMeta->type = $this->getNormalizedType($column->type);
            $fieldMeta->nonNull = !$column->nullable;
        }

        /** @var Id $id */
        if ($column = $this->reader->getPropertyAnnotation($field, Id::class)) {
            $fieldMeta->type = Type::ID;
            $fieldMeta->nonNull = true;
        }

        /** @var OneToOne $oneToOne */
        if ($oneToOne = $this->reader->getPropertyAnnotation($field, OneToOne::class)) {
            $fieldMeta->type = $oneToOne->targetEntity;
            $fieldMeta->nonNull = true;
        }

        /** @var OneToMany $oneToMany */
        if ($oneToMany = $this->reader->getPropertyAnnotation($field, OneToMany::class)) {
            $fieldMeta->type = $oneToMany->targetEntity;
            $fieldMeta->list = true;
        }

        /** @var ManyToOne $manyToOne */
        if ($manyToOne = $this->reader->getPropertyAnnotation($field, ManyToOne::class)) {
            $fieldMeta->type = $manyToOne->targetEntity;
        }

        /** @var ManyToMany $manyToMany */
        if ($manyToMany = $this->reader->getPropertyAnnotation($field, ManyToMany::class)) {
            $fieldMeta->type = $manyToMany->targetEntity;
            $fieldMeta->list = true;
        }

        /** @var Embedded $embedded */
        if ($embedded = $this->reader->getPropertyAnnotation($field, Embedded::class)) {
            $fieldMeta->type = $embedded->class;
        }

        return $fieldMeta;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getNormalizedType(?string $type):?string
    {
        switch ($type) {
            case DoctrineType::BOOLEAN:
                $type = Type::BOOLEAN;
                break;
            case DoctrineType::DECIMAL:
            case DoctrineType::FLOAT:
                $type = Type::FLOAT;
                break;
            case DoctrineType::INTEGER:
            case DoctrineType::BIGINT:
            case DoctrineType::SMALLINT:
                $type = Type::INT;
                break;
            case DoctrineType::STRING:
            case DoctrineType::TEXT:
                $type = Type::STRING;
                break;
            case DoctrineType::DATE:
            case DoctrineType::DATETIME:
                $type = 'DateTime';
                break;
        }

        return $type;
    }
}
