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
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchPhrase;
use Elastica\Query\MatchPhrasePrefix;
use Elastica\Query\MatchQuery;
use Elastica\Query\Prefix;
use Elastica\Query\QueryString;
use Elastica\Query\Term;
use Elastica\Query\Wildcard;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Filter\FilterInterface;
use Ynlo\GraphQLBundle\Model\Filter\StringComparisonExpression;
use Ynlo\GraphQLBundle\Type\StringComparisonOperatorType;
use Ynlo\GraphQLBundle\Util\ElasticUtil;

/**
 * string filter to compare strings and filter by them
 */
class StringFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, $qb, $condition)
    {
        if (!$condition instanceof StringComparisonExpression) {
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
            $this->applyDoctrineFilter($qb, $alias, $column, $condition);
        } else {
            $this->applyElasticFilter($qb, $column, $condition);
        }
    }

    /**
     * @param BoolQuery                  $query
     * @param string                     $column
     * @param StringComparisonExpression $condition
     */
    protected function applyElasticFilter(BoolQuery $query, $column, StringComparisonExpression $condition): void
    {
        switch ($condition->getOp()) {
            case StringComparisonOperatorType::CONTAINS:
                if (!empty($condition->getValues())) {
                    $bool = new BoolQuery();
                    foreach ($condition->getValues() as $value) {
                        $columnQuery = new Wildcard($column, sprintf('*%s*', ElasticUtil::escapeReservedChars($value)));
                        $bool->addShould($columnQuery);
                    }
                    $query->addMust($bool);

                } else {
                    $columnQuery = new Wildcard($column, sprintf('*%s*', ElasticUtil::escapeReservedChars($condition->getValue())));
                    $query->addMust($columnQuery);
                }
                break;
            case StringComparisonOperatorType::EQUAL:
                $columnQuery = new MatchPhrase();
                $columnQuery->setField($column, ElasticUtil::escapeReservedChars($condition->getValue()));
                $query->addMust($columnQuery);
                break;
            case StringComparisonOperatorType::STARTS_WITH:
                //TODO
            case StringComparisonOperatorType::ENDS_WITH:
                //TODO
        }
    }

    /**
     * @param QueryBuilder               $qb
     * @param string                     $alias
     * @param string                     $column
     * @param StringComparisonExpression $condition
     */
    protected function applyDoctrineFilter(QueryBuilder $qb, $alias, $column, StringComparisonExpression $condition): void
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
