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

use Doctrine\ORM\Query\Expr\Orx;
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
        if ($context->getField()->getOriginType() === 'ReflectionMethod') {
            $column = $context->getField()->getName();
        }

        $this->applyFilter($qb, $alias, $column, $condition);
    }

    /**
     * @param QueryBuilder               $qb
     * @param string                     $alias
     * @param string                     $column
     * @param StringComparisonExpression $condition
     */
    protected function applyFilter(QueryBuilder $qb, $alias, $column, StringComparisonExpression $condition): void
    {
        switch ($condition->getOp()) {
            case StringComparisonOperatorType::EQUAL:
                if ($condition->getValue()) {
                    $qb->andWhere("{$alias}.{$column} = '{$condition->getValue()}'");
                } elseif ($condition->getValues()) {
                    $orx = new Orx();
                    foreach ($condition->getValues() as $value) {
                        $orx->add("{$alias}.{$column} = '{$value}'");
                    }
                    $qb->andWhere($orx);
                }
                break;
            case StringComparisonOperatorType::CONTAINS:
                if ($condition->getValue()) {
                    $qb->andWhere("{$alias}.{$column} LIKE '%{$condition->getValue()}%'");
                } elseif ($condition->getValues()) {
                    $orx = new Orx();
                    foreach ($condition->getValues() as $value) {
                        $orx->add("{$alias}.{$column} LIKE '%{$value}%'");
                    }
                    $qb->andWhere($orx);
                }
                break;
            case StringComparisonOperatorType::STARTS_WITH:
                if ($condition->getValue()) {
                    $qb->andWhere("{$alias}.{$column} LIKE '{$condition->getValue()}%'");
                } elseif ($condition->getValues()) {
                    $orx = new Orx();
                    foreach ($condition->getValues() as $value) {
                        $orx->add("{$alias}.{$column} LIKE '{$value}%'");
                    }
                    $qb->andWhere($orx);
                }
                break;
            case StringComparisonOperatorType::ENDS_WITH:
                if ($condition->getValue()) {
                    $qb->andWhere("{$alias}.{$column} LIKE '%{$condition->getValue()}'");
                } elseif ($condition->getValues()) {
                    $orx = new Orx();
                    foreach ($condition->getValues() as $value) {
                        $orx->add("{$alias}.{$column} LIKE '%{$value}'");
                    }
                    $qb->andWhere($orx);
                }
                break;
        }
    }
}
