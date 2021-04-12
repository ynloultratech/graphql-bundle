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
use Elastica\Query\Term;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Filter\FilterInterface;
use Ynlo\GraphQLBundle\Model\Filter\EnumComparisonExpression;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;

class EnumFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, $qb, $condition)
    {
        if (!$condition instanceof EnumComparisonExpression) {
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

    /**
     * @param BoolQuery                $qb
     * @param string                   $column
     * @param EnumComparisonExpression $condition
     */
    protected function applyElasticFilter(BoolQuery $qb, $column, EnumComparisonExpression $condition): void
    {
        $boolQuery = new BoolQuery();
        switch ($condition->getOp()) {
            case NodeComparisonOperatorType::IN:
                foreach ($condition->getValues() as $value) {
                    $boolQuery->addShould((new Term())->setTerm($column, $value));
                }
                break;
            case NodeComparisonOperatorType::NIN:
                foreach ($condition->getValues() as $value) {
                    $boolQuery->addMustNot((new Term())->setTerm($column, $value));
                }
                break;
        }
        $qb->addMust($boolQuery);
    }
}
