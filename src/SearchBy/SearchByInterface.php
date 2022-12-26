<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\SearchBy;

use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

interface SearchByInterface
{
    public const PARTIAL_SEARCH = 'partial';
    public const EXACT_MATCH = 'exact';
    public const INTEGER = 'integer';

    /**
     * @param SearchByContext $context
     * @param QueryBuilder    $qb     Query builder instance to make the filter
     * @param Orx             $orx    or condition to add search param
     * @param string          $alias  root query alias
     * @param string          $column search column
     * @param string          $mode   search mode (PARTIAL_SEARCH, EXACT_MATCH constants)
     * @param string          $search search string
     *
     * @return mixed
     */
    public function __invoke(SearchByContext $context, QueryBuilder $qb, Orx $orx, $alias, string $column, string $mode, string $search);
}
