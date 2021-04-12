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
use Elastica\Query\Term;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Filter\FilterInterface;

class BooleanFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, $qb, $condition)
    {
        if (!\is_bool($condition)) {
            throw new \RuntimeException('Invalid filter condition');
        }

        if (!$context->getField() || !$context->getField()->getName()) {
            throw new \RuntimeException('There are not valid field related to this filter.');
        }

        $column = $this->resolveColumn($context);
        if ($qb instanceof QueryBuilder) {
            $alias = $qb->getRootAliases()[0];
            $this->applyFilter($qb, $alias, $column, $condition);
        } else {
            $this->applyElasticFilter($qb, $column, $condition);
        }
    }

    /**
     * @param FilterContext $context
     *
     * @return string
     */
    protected function resolveColumn(FilterContext $context): string
    {
        $column = $context->getField()->getOriginName();
        if (!$column || $context->getField()->getOriginType() === 'ReflectionMethod') {
            $column = $context->getField()->getName();
        }

        return $column;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param string       $column
     * @param bool         $condition
     */
    protected function applyFilter(QueryBuilder $qb, $alias, $column, bool $condition): void
    {
        $value = (int) $condition;
        $qb->andWhere("{$alias}.{$column} = $value");
    }

    /**
     * @param BoolQuery $qb
     * @param string    $column
     * @param bool      $condition
     */
    protected function applyElasticFilter(BoolQuery $qb, $column, bool $condition): void
    {
        $columnQuery = new Term();
        $columnQuery->setTerm($column, $condition);
        $qb->addMust($columnQuery);
    }
}
