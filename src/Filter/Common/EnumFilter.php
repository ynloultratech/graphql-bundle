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
use Ynlo\GraphQLBundle\Model\Filter\EnumComparisonExpression;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;

class EnumFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, QueryBuilder $qb, $condition)
    {
        if (!$condition instanceof EnumComparisonExpression) {
            throw new \RuntimeException('Invalid filter condition');
        }

        if (!$context->getField() || !$context->getField()->getName()) {
            throw new \RuntimeException('There are not valid field related to this filter.');
        }

        $alias = $qb->getRootAliases()[0];
        $column = $context->getField()->getOriginName();
        if (!$column || $context->getField()->getOriginType() === 'ReflectionMethod') {
            $column = $context->getField()->getName();
        }

        $this->applyFilter($qb, $alias, $column, $condition);
    }

    /**
     * @param QueryBuilder             $qb
     * @param string                   $alias
     * @param string                   $column
     * @param EnumComparisonExpression $condition
     */
    protected function applyFilter(QueryBuilder $qb, $alias, $column, EnumComparisonExpression $condition): void
    {
        switch ($condition->getOp()) {
            case NodeComparisonOperatorType::IN:
                $qb->andWhere($qb->expr()->in("{$alias}.{$column}", $condition->getValues()));
                break;
            case NodeComparisonOperatorType::NIN:
                $qb->andWhere($qb->expr()->notIn("{$alias}.{$column}", $condition->getValues()));
                break;
        }
    }
}
