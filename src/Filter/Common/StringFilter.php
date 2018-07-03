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
use Ynlo\GraphQLBundle\Model\Filter\StringComparisonExpression;
use Ynlo\GraphQLBundle\Type\StringComparisonOperatorType;

/**
 * string filter to compare strings and filter by them
 */
class StringFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, QueryBuilder $qb, $condition)
    {
        if (!$condition instanceof StringComparisonExpression) {
            throw new \RuntimeException('Invalid filter condition');
        }

        if (!$context->getField() || !$context->getField()->getName()) {
            throw new \RuntimeException('There are not valid field related to this filter.');
        }

        $alias = $qb->getRootAliases()[0];
        $column = $context->getField()->getOriginName();
        switch ($condition->getOp()) {
            case StringComparisonOperatorType::EQUAL:
                $qb->andWhere("{$alias}.{$column} = '{$condition->getValue()}'");
                break;
            case StringComparisonOperatorType::CONTAINS:
                $qb->andWhere("{$alias}.{$column} LIKE '%{$condition->getValue()}%'");
                break;
            case StringComparisonOperatorType::STARTS_WITH:
                $qb->andWhere("{$alias}.{$column} LIKE '{$condition->getValue()}%'");
                break;
            case StringComparisonOperatorType::ENDS_WITH:
                $qb->andWhere("{$alias}.{$column} LIKE '%{$condition->getValue()}'");
                break;
        }
    }
}
