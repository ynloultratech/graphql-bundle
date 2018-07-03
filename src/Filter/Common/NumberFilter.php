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
use Ynlo\GraphQLBundle\Model\Filter\FloatComparisonExpression;
use Ynlo\GraphQLBundle\Type\NumberComparisonOperatorType;

/**
 * Filter to compare numeric values
 */
class NumberFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, QueryBuilder $qb, $condition)
    {
        if (!$condition instanceof FloatComparisonExpression) {
            throw new \RuntimeException('Invalid filter condition');
        }

        if (!$context->getField() || !$context->getField()->getName()) {
            throw new \RuntimeException('There are not valid field related to this filter.');
        }

        $alias = $qb->getRootAliases()[0];
        $column = $context->getField()->getOriginName();
        switch ($condition->getOp()) {
            case NumberComparisonOperatorType::EQ:
                $qb->andWhere("{$alias}.{$column} = {$condition->getValue()}");
                break;
            case NumberComparisonOperatorType::NEQ:
                $qb->andWhere("{$alias}.{$column} <> {$condition->getValue()}");
                break;
            case NumberComparisonOperatorType::GT:
                $qb->andWhere("{$alias}.{$column} > {$condition->getValue()}");
                break;
            case NumberComparisonOperatorType::GTE:
                $qb->andWhere("{$alias}.{$column} >= {$condition->getValue()}");
                break;
            case NumberComparisonOperatorType::LT:
                $qb->andWhere("{$alias}.{$column} < {$condition->getValue()}");
                break;
            case NumberComparisonOperatorType::LTE:
                $qb->andWhere("{$alias}.{$column} <= {$condition->getValue()}");
                break;
            case NumberComparisonOperatorType::BETWEEN:
                $max = $condition->getMaxValue() ?? $condition->getValue();
                $qb->andWhere("{$alias}.{$column} BETWEEN {$condition->getValue()} AND {$max}");
                break;
        }
    }
}
