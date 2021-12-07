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
use Doctrine\DBAL\Types\Types as DBALTypes;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Type\Types;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Decorate a field definition using doctrine annotations
 */
class DoctrineFieldDefinitionDecorator implements FieldDefinitionDecoratorInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * DoctrineFieldDefinitionDecorator constructor.
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

        if ($field instanceof \ReflectionMethod) {
            return;
        }

        $pagination = $definition->getMeta('pagination') ?: [];
        $targetNode = null;

        /** @var Column $column */
        if ($column = $this->reader->getPropertyAnnotation($field, Column::class)) {
            $type = $this->getGraphQLType($column->type);
            $definition->setType(TypeUtil::normalize($type));
            $definition->setList(TypeUtil::isTypeList($type));
            $definition->setNonNullList(TypeUtil::isTypeNonNullList($type));
            $definition->setNonNull(!$column->nullable);
        }

        /** @var Id $id */
        if ($field->getName() === 'id' && ($column = $this->reader->getPropertyAnnotation($field, Id::class))) {
            $definition->setType(Types::ID);
            $definition->setNonNull(true);
        }

        /** @var OneToOne $oneToOne */
        if ($oneToOne = $this->reader->getPropertyAnnotation($field, OneToOne::class)) {
            $definition->setType($targetNode = $oneToOne->targetEntity);
        }

        /** @var OneToMany $oneToMany */
        if ($oneToMany = $this->reader->getPropertyAnnotation($field, OneToMany::class)) {
            $definition->setType($targetNode = $oneToMany->targetEntity);
            $definition->setList(true);
            if ($oneToMany->fetch === 'EXTRA_LAZY') {
                $pagination['target'] = $pagination['target'] ?? $targetNode;
                $pagination['parent_field'] = $pagination['parent_field'] ?? $oneToMany->mappedBy;
                $pagination['parent_relation'] = $pagination['parent_relation'] ?? 'ONE_TO_MANY';
            }
        }

        /** @var ManyToOne $manyToOne */
        if ($manyToOne = $this->reader->getPropertyAnnotation($field, ManyToOne::class)) {
            $definition->setType($targetNode = $manyToOne->targetEntity);
        }

        /** @var ManyToMany $manyToMany */
        if ($manyToMany = $this->reader->getPropertyAnnotation($field, ManyToMany::class)) {
            $definition->setType($targetNode = $manyToMany->targetEntity);
            $definition->setList(true);
            if ($manyToMany->fetch === 'EXTRA_LAZY') {
                $pagination['target'] = $pagination['target'] ?? $targetNode;
                $pagination['parent_field'] = $pagination['parent_field'] ?? $manyToMany->mappedBy ?? $manyToMany->inversedBy;
                $pagination['parent_relation'] = $pagination['parent_relation'] ?? 'MANY_TO_MANY';
            }
        }

        /** @var Embedded $embedded */
        if ($embedded = $this->reader->getPropertyAnnotation($field, Embedded::class)) {
            $definition->setType($embedded->class);
        }

        if ($definition->isList() && $pagination) {
            $definition->setMeta('pagination', $pagination);
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getGraphQLType(?string $type): ?string
    {
        switch ($type) {
            case DBALTypes::BOOLEAN:
                $type = Types::BOOLEAN;
                break;
            case DBALTypes::DECIMAL:
            case DBALTypes::FLOAT:
                $type = Types::FLOAT;
                break;
            case DBALTypes::INTEGER:
            case DBALTypes::BIGINT:
            case DBALTypes::SMALLINT:
                $type = Types::INT;
                break;
            case DBALTypes::STRING:
            case DBALTypes::TEXT:
            case DBALTypes::GUID:
                $type = Types::STRING;
                break;
            case DBALTypes::ARRAY:
            case DBALTypes::SIMPLE_ARRAY:
            case DBALTypes::JSON:
            /** @deprecated json_array type is deprecated, use {@see self::JSON} instead. */
            case 'json_array':
                $type = Types::nonNull(Types::listOf(Types::STRING));
                break;
            case DBALTypes::TIME_MUTABLE:
            case DBALTypes::TIME_IMMUTABLE:
                $type = Types::TIME;
                break;
            case DBALTypes::DATE_MUTABLE:
                $type = Types::DATE;
                break;
            case DBALTypes::DATETIME_MUTABLE:
                $type = Types::DATETIME;
                break;
        }

        return $type;
    }
}
