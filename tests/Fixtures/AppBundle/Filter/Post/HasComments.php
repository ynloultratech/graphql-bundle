<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Filter\Post;

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Filter\FilterInterface;

/**
 * @GraphQL\Filter(
 *     type="bool",
 *     description="View only posts with or without comments",
 *     field="comments"
 * )
 */
class HasComments implements FilterInterface
{
    public function __invoke(FilterContext $context, $qb, $condition)
    {
        $alias = $qb->getRootAliases()[0];
        $qb->leftJoin("{$alias}.comments", 'comments');

        if ($condition) {
            $qb->andWhere("{$alias}.comments is not empty");
        } else {
            $qb->andWhere("{$alias}.comments is empty");
        }
    }
}