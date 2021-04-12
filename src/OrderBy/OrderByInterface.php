<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\OrderBy;

use Doctrine\ORM\QueryBuilder;
use Elastica\Query;
use Ynlo\GraphQLBundle\Model\OrderBy;

interface OrderByInterface
{
    /**
     * @param OrderByContext     $context
     * @param QueryBuilder|Query $qb      Query builder instance to make the filter
     * @param string             $alias   root query alias
     * @param OrderBy            $orderBy field and direction
     *
     * @return mixed
     */
    public function __invoke(OrderByContext $context, $qb, $alias, OrderBy $orderBy);
}
