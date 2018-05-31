<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Query\User;

use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Query\Node\AllNodesWithPagination;

/**
 * @GraphQL\Query(type="[]", options={
 *     @GraphQL\Plugin\Pagination(limit=30),
 *     @GraphQL\Plugin\Endpoints("aa")
 * })
 */
class Admins extends AllNodesWithPagination
{
    /**
     * {@inheritdoc}
     */
    public function configureQuery(QueryBuilder $qb)
    {
        $qb->andWhere('o.type = :adminType')
           ->setParameter('adminType', User::TYPE_ADMIN);
    }
}
