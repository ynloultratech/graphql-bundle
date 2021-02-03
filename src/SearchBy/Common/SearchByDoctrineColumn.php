<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\SearchBy\Common;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\SearchBy\SearchByContext;
use Ynlo\GraphQLBundle\SearchBy\SearchByInterface;

class SearchByDoctrineColumn implements SearchByInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(SearchByContext $context, QueryBuilder $qb, Orx $orx, $alias, string $column, string $mode, string $search)
    {
        while (strpos($column, '.') !== false) {
            [$child, $column] = explode('.', $column, 2);
            $parentAlias = $alias;
            $alias = 'searchJoin'.ucfirst($child).mt_rand();
            if (!\in_array($alias, $qb->getAllAliases(), true)) {
                $qb->leftJoin("{$parentAlias}.{$child}", $alias);
            }
        }

        if (self::PARTIAL_SEARCH === $mode) {
            //search each word separate
            $searchArray = explode(' ', $search);

            $partialAnd = new Andx();
            foreach ($searchArray as $index => $q) {
                $partialAnd->add("$alias.$column LIKE :query_search_$index");
                $qb->setParameter("query_search_$index", '%'.addcslashes($q, '%_').'%');
            }
            $orx->add($partialAnd);
        } else {
            $orx->add("$alias.$column = :query_search");
            $qb->setParameter('query_search', trim($search));
        }
    }
}