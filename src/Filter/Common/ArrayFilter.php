<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter\Common;

use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Filter\FilterInterface;
use Ynlo\GraphQLBundle\Model\Filter\ArrayComparisonExpression;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;

/**
 * This filter is used to match values using the doctrine array column type
 */
class ArrayFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, QueryBuilder $qb, $condition)
    {
        if (!$condition instanceof ArrayComparisonExpression) {
            throw new \RuntimeException('Invalid filter condition');
        }

        if (!$context->getField() || !$context->getField()->getName()) {
            throw new \RuntimeException('There are not valid field related to this filter.');
        }

        $entity = $context->getNode()->getClass();
        $metadata = $qb->getEntityManager()->getClassMetadata($entity);
        $column = $context->getField()->getOriginName();

        if (!$metadata->hasField($column)) {
            throw new \RuntimeException(sprintf('There are not valid column in %s called %s.', $entity, $column));
        }

        $columnMapping = $metadata->getFieldMapping($column);

        $alias = $qb->getRootAliases()[0];
        switch ($condition->getOp()) {
            case NodeComparisonOperatorType::IN:
                foreach ($condition->getValues() as $value) {
                    switch ($columnMapping['type']) {
                        case 'array':
                        case 'json_array':
                            $qb->andWhere($qb->expr()->like("{$alias}.{$column}", "'%\"{$value}\"%'"));
                            break;
                        case 'simple_array':
                            $or = $qb->expr()->orX();
                            $or->add($qb->expr()->eq("{$alias}.{$column}", "'%$value%'")); // only one value
                            $or->add($qb->expr()->like("{$alias}.{$column}", "'$value,%'")); //first value
                            $or->add($qb->expr()->like("{$alias}.{$column}", "'%,$value,%'")); //middle value
                            $or->add($qb->expr()->like("{$alias}.{$column}", "'%,$value'")); // latest value
                            $qb->andWhere($or);
                            break;
                    }
                }
                break;
        }
    }
}
