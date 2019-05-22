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

class BooleanFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, QueryBuilder $qb, $condition)
    {
        if (!\is_bool($condition)) {
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

        $condition = (int) $condition;
        $qb->andWhere("{$alias}.{$column} = $condition");
    }
}
