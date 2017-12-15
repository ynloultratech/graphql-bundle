<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Query\Category\Field;

use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Query\Node\AllNodesWithPagination;

/**
 * @GraphQL\Field(type="[Post]", options={"pagination": {"parent_field": "categories", "parent_relation": "MANY_TO_MANY"} })
 * @GraphQL\Argument(name="status", type="PostStatus!")
 */
class PostsByStatus extends AllNodesWithPagination
{
    /**
     * {@inheritDoc}
     */
    public function configureQuery(QueryBuilder $qb)
    {
        $status = $this->context->getArgs()['status'];
        $qb->andWhere('o.status = :status')
           ->setParameter('status', $status);
    }
}