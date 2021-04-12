<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Pagination;

use Doctrine\ORM\QueryBuilder;
use Elastica\Query;
use Ynlo\GraphQLBundle\Model\ConnectionInterface;

interface CursorPaginatorInterface
{
    /**
     * @param QueryBuilder|Query  $query
     * @param PaginationRequest   $pagination
     * @param ConnectionInterface $connection
     */
    public function paginate($query, PaginationRequest $pagination, ConnectionInterface $connection);
}
