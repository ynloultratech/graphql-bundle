<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter\Resolver;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\ObjectManager;
use GraphQL\Type\Definition\EnumType;
use Ynlo\GraphQLBundle\Annotation\Filter;
use Ynlo\GraphQLBundle\Definition\ClassAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\EnumDefinition;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\Common\ArrayFilter;
use Ynlo\GraphQLBundle\Filter\Common\BooleanFilter;
use Ynlo\GraphQLBundle\Filter\Common\DateFilter;
use Ynlo\GraphQLBundle\Filter\Common\EnumFilter;
use Ynlo\GraphQLBundle\Filter\Common\NodeFilter;
use Ynlo\GraphQLBundle\Filter\Common\NumberFilter;
use Ynlo\GraphQLBundle\Filter\Common\StringFilter;
use Ynlo\GraphQLBundle\Filter\FilterResolverInterface;
use Ynlo\GraphQLBundle\Model\Filter\ArrayComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\DateComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\DateTimeComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\EnumComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\FloatComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\IntegerComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\NodeComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\StringComparisonExpression;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;
use Ynlo\GraphQLBundle\Type\Types;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Resolve filters automatically based on GraphQL field definitions directly related to Entity columns
 */
class DoctrineORMFilterResolver implements FilterResolverInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * DoctrineORMFilterResolver constructor.
     *
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ExecutableDefinitionInterface $executableDefinition,ObjectDefinitionInterface $node, Endpoint $endpoint): array
    {
        $class = $node->getClass();
        $filters = [];
        try {
            if (!$class || !($metaData = $this->manager->getClassMetadata($class))) {
                return $filters;
            }

            foreach ($node->getFields() as $field) {
                if (!$metaData->hasField($field->getName())
                    && !$metaData->hasAssociation($field->getName())
                    && !$metaData->hasField($field->getOriginName())
                    && !$metaData->hasAssociation($field->getOriginName())) {
                    continue;
                }

                //ignore ID
                if ($field->getName() === 'id') {
                    continue;
                }

                $filter = new Filter();
                $filter->name = $filter->field = $field->getName();

                //simple fields
                switch (TypeUtil::normalize($field->getType())) {
                    case Types::STRING:
                        $filter->resolver = StringFilter::class;
                        $filter->type = StringComparisonExpression::class;
                        if ($field->isList()) {
                            $filter->resolver = ArrayFilter::class;
                            $filter->type = ArrayComparisonExpression::class;
                        }
                        break;
                    case Types::BOOLEAN:
                        $filter->resolver = BooleanFilter::class;
                        $filter->type = Types::BOOLEAN;
                        break;
                    case Types::DATE:
                        $filter->resolver = DateFilter::class;
                        $filter->type = DateComparisonExpression::class;
                        break;
                    case Types::DATETIME:
                        $filter->resolver = DateFilter::class;
                        $filter->type = DateTimeComparisonExpression::class;
                        break;
                    case Types::INT:
                        $filter->resolver = NumberFilter::class;
                        $filter->type = IntegerComparisonExpression::class;
                        break;
                    case Types::FLOAT:
                        $filter->resolver = NumberFilter::class;
                        $filter->type = FloatComparisonExpression::class;
                        break;
                }

                //relations and enums
                if (!$filter->resolver && $endpoint->hasType($field->getType())) {
                    $relatedNode = $endpoint->getType($field->getType());

                    //enum registered using enum definition
                    if ($relatedNode instanceof EnumDefinition) {
                        $enumName = $relatedNode->getName();
                        foreach ($relatedNode->getValues() as $value) {
                            $enumValues[] = $value->getName();
                        }
                        $filter = $this->createEnumFilter($endpoint, $field, $enumName, $enumValues);
                        $filter->field = $field->getName();
                    } elseif ($relatedNode instanceof ClassAwareDefinitionInterface) {
                        $relatedEntity = $relatedNode->getClass();
                        try {
                            if ($this->manager->getClassMetadata($relatedEntity)) {
                                $filter->resolver = NodeFilter::class;
                                $filter->type = NodeComparisonExpression::class;
                            }
                        } catch (MappingException $exception) {
                            //ignore
                        }
                    }
                }

                //enum registered as internal GraphQL type
                if (!$filter->resolver && TypeRegistry::has($field->getType())
                    && ($enumType = TypeRegistry::get($field->getType()))
                    && $enumType instanceof EnumType) {
                    $enumName = $enumType->name;
                    foreach ($enumType->getValues() as $value) {
                        $enumValues[] = $value->name;
                    }
                    $filter = $this->createEnumFilter($endpoint, $field, $enumName, $enumValues);
                }

                if ($filter->resolver) {
                    $filters[] = $filter;
                }
            }

            return $filters;
        } catch (MappingException $exception) {
            return [];
        }
    }

    /**
     * @param Endpoint        $endpoint
     * @param FieldDefinition $field
     * @param string          $enumName
     * @param array           $enumValues
     *
     * @return Filter
     */
    private function createEnumFilter(Endpoint $endpoint, FieldDefinition $field, string $enumName, array $enumValues): Filter
    {
        $name = "{$enumName}ComparisonExpression";
        if ($endpoint->hasType($name)) {
            $condition = $endpoint->getType($name);
        } else {
            /** @var InputObjectDefinition $condition */
            $condition = unserialize(serialize($endpoint->getType(EnumComparisonExpression::class)), ['allowed_classes' => true]);
            $condition->setName($name);
            $condition->getField('values')->setType($enumName)->setList(true);
            $description = $condition->getDescription();
            $description = str_replace('\'{VALUE_1}\'', array_values($enumValues)[0], $description);
            if (isset(array_values($enumValues)[1])) {
                $description = str_replace('\'{VALUE_2}\'', array_values($enumValues)[1], $description);
            } else {
                $description = str_replace(', \'{VALUE_2}\'', null, $description);
            }
            $condition->setDescription($description);
            $endpoint->add($condition);
        }
        $filter = new Filter();
        $filter->name = $filter->field = $field->getName();
        $filter->resolver = EnumFilter::class;
        $filter->type = $condition->getName();

        return $filter;
    }
}
