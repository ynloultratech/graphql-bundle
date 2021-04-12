<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\OrderBy\Common;

use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\OrderBy\OrderByContext;
use Ynlo\GraphQLBundle\OrderBy\OrderByInterface;

class OrderByRelatedField implements OrderByInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(OrderByContext $context, $qb, $alias, OrderBy $orderBy)
    {
        $column = $orderBy->getField();
        [$relation, $column] = explode('.', $column);
        $childAlias = 'orderBy'.ucfirst($relation);
        $qb->leftJoin("{$alias}.{$relation}", $childAlias);
        $qb->addOrderBy("$childAlias.$column", $orderBy->getDirection());
    }
}
