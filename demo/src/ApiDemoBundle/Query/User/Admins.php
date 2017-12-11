<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Query\User;

use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\User;
use Ynlo\GraphQLBundle\Query\Node\AllNodesConnection;

/**
 * @GraphQL\Query()
 * @GraphQL\Connection()
 */
class Admins extends AllNodesConnection
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
