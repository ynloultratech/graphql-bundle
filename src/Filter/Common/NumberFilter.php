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
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use Elastica\Query\Range;
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
    public function __invoke(FilterContext $context, $qb, $condition)
    {
        if (!$condition instanceof FloatComparisonExpression) {
            throw new \RuntimeException('Invalid filter condition');
        }

        if (!$context->getField() || !$context->getField()->getName()) {
            throw new \RuntimeException('There are not valid field related to this filter.');
        }


        $column = $context->getField()->getOriginName();
        if (!$column || $context->getField()->getOriginType() === 'ReflectionMethod') {
            $column = $context->getField()->getName();
        }
        if ($qb instanceof QueryBuilder) {
            $alias = $qb->getRootAliases()[0];
            $this->applyFilter($qb, $alias, $column, $condition);
        } else {
            $this->applyElasticFilter($qb, $column, $condition);
        }
    }

    /**
     * @param QueryBuilder              $qb
     * @param string                    $alias
     * @param string                    $column
     * @param FloatComparisonExpression $condition
     */
    protected function applyFilter(QueryBuilder $qb, $alias, $column, FloatComparisonExpression $condition): void
    {
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

    protected function applyElasticFilter(BoolQuery $qb, $column, FloatComparisonExpression $condition): void
    {
        switch ($condition->getOp()) {
            case NumberComparisonOperatorType::EQ:
                $qb->addMust((new MatchQuery())->setField($column, $condition->getValue()));
                break;
            case NumberComparisonOperatorType::NEQ:
                $qb->addMustNot((new MatchQuery())->setField($column, $condition->getValue()));
                break;
            case NumberComparisonOperatorType::GT:
                $qb->addMust((new Range())->addField($column, ['gt' => $condition->getValue()]));
                break;
            case NumberComparisonOperatorType::GTE:
                $qb->addMust((new Range())->addField($column, ['gte' => $condition->getValue()]));
                break;
            case NumberComparisonOperatorType::LT:
                $qb->addMust((new Range())->addField($column, ['lt' => $condition->getValue()]));
                break;
            case NumberComparisonOperatorType::LTE:
                $qb->addMust((new Range())->addField($column, ['lte' => $condition->getValue()]));
                break;
            case NumberComparisonOperatorType::BETWEEN:
                $qb->addMust((new Range())->addField($column, ['gte' => $condition->getValue()]));
                $qb->addMust((new Range())->addField($column, ['lte' => $condition->getMaxValue()]));
                break;
        }
    }
}
