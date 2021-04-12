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
use Elastica\Aggregation\DateRange;
use Elastica\Query\BoolQuery;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Filter\FilterInterface;
use Ynlo\GraphQLBundle\Model\Filter\DateComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\EnumComparisonExpression;
use Ynlo\GraphQLBundle\Type\DateComparisonOperatorType;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;

class DateFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, $qb, $condition)
    {
        if (!$condition instanceof DateComparisonExpression) {
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
     * @param QueryBuilder             $qb
     * @param string                   $alias
     * @param string                   $column
     * @param DateComparisonExpression $condition
     */
    protected function applyFilter(QueryBuilder $qb, $alias, $column, DateComparisonExpression $condition): void
    {
        $date = $condition->getDate()->format('Y-m-d H:i:s');
        $maxDate = $date;
        if ($condition->getMaxDate()) {
            $maxDate = $condition->getMaxDate()->format('Y-m-d H:i:s');
        }

        switch ($condition->getOp()) {
            case DateComparisonOperatorType::AFTER:
                if ($condition->isStrict()) {
                    $qb->andWhere($qb->expr()->gt("{$alias}.{$column}", "'$date'"));
                } else {
                    $qb->andWhere($qb->expr()->gte("{$alias}.{$column}", "'$date'"));
                }
                break;
            case DateComparisonOperatorType::BEFORE:
                if ($condition->isStrict()) {
                    $qb->andWhere($qb->expr()->lt("{$alias}.{$column}", "'$date'"));
                } else {
                    $qb->andWhere($qb->expr()->lte("{$alias}.{$column}", "'$date'"));
                }
                break;
            case DateComparisonOperatorType::BETWEEN:
                if ($condition->isStrict()) {
                    $qb->andWhere($qb->expr()->gt("{$alias}.{$column}", "'$date'"));
                    $qb->andWhere($qb->expr()->lt("{$alias}.{$column}", "'$maxDate'"));
                } else {
                    $qb->andWhere($qb->expr()->gte("{$alias}.{$column}", "'$date'"));
                    $qb->andWhere($qb->expr()->lte("{$alias}.{$column}", "'$maxDate'"));
                }
                break;
            case DateComparisonOperatorType::NOT_BETWEEN:
                if ($condition->isStrict()) {
                    $orx = new Orx();
                    $orx->add($qb->expr()->lt("{$alias}.{$column}", "'$date'"));
                    $orx->add($qb->expr()->gt("{$alias}.{$column}", "'$maxDate'"));
                    $qb->andWhere($orx);
                } else {
                    $orx = new Orx();
                    $orx->add($qb->expr()->lte("{$alias}.{$column}", "'$date'"));
                    $orx->add($qb->expr()->gte("{$alias}.{$column}", "'$maxDate'"));
                    $qb->andWhere($orx);
                }
                break;
        }
    }

    /**
     * @param BoolQuery                $qb
     * @param string                   $column
     * @param DateComparisonExpression $condition
     */
    protected function applyElasticFilter(BoolQuery $qb, $column, DateComparisonExpression $condition): void
    {
        $date = $condition->getDate()->format('Y-m-d H:i:s');
        $maxDate = $date;
        if ($condition->getMaxDate()) {
            $maxDate = $condition->getMaxDate()->format('Y-m-d H:i:s');
        }

        switch ($condition->getOp()) {
            case DateComparisonOperatorType::AFTER:
                if ($condition->isStrict()) {
                    $query = new Range();
                    $query->addField($column, ['gt' => $date]);
                    $qb->addMust($query);
                } else {
                    $query = new Range();
                    $query->addField($column, ['gte' => $date]);
                    $qb->addMust($query);
                }
                break;
            case DateComparisonOperatorType::BEFORE:
                if ($condition->isStrict()) {
                    $query = new Range();
                    $query->addField($column, ['lt' => $date]);
                    $qb->addMust($query);
                } else {
                    $query = new Range();
                    $query->addField($column, ['lte' => $date]);
                    $qb->addMust($query);
                }
                break;
            case DateComparisonOperatorType::BETWEEN:
                if ($condition->isStrict()) {
                    $boolQuery = new BoolQuery();
                    $boolQuery->addMust((new Range())->addField($column, ['gte' => $date]));
                    $boolQuery->addMust((new Range())->addField($column, ['lte' => $maxDate]));
                    $qb->addMust($boolQuery);
                } else {
                    $boolQuery = new BoolQuery();
                    $boolQuery->addMust((new Range())->addField($column, ['gt' => $date]));
                    $boolQuery->addMust((new Range())->addField($column, ['lt' => $maxDate]));
                    $qb->addMust($boolQuery);
                }
                break;
            case DateComparisonOperatorType::NOT_BETWEEN:
                if ($condition->isStrict()) {
                    $boolQuery = new BoolQuery();
                    $boolQuery->addShould((new Range())->addField($column, ['lte' => $date]));
                    $boolQuery->addShould((new Range())->addField($column, ['gte' => $maxDate]));
                    $qb->addMust($boolQuery);
                } else {
                    $boolQuery = new BoolQuery();
                    $boolQuery->addShould((new Range())->addField($column, ['lt' => $date]));
                    $boolQuery->addShould((new Range())->addField($column, ['gt' => $maxDate]));
                    $qb->addMust($boolQuery);
                }
                break;
        }
    }
}
