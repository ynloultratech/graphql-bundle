<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Elastica\Query\BoolQuery;

interface FilterInterface
{
    /**
     * @param FilterContext          $context
     * @param QueryBuilder|BoolQuery $qb        Query builder instance to make the filter
     * @param mixed                  $condition the condition data entered by users using the given inputType
     *
     * @return mixed
     */
    public function __invoke(FilterContext $context, $qb, $condition);
}
