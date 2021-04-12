<?php
/*
 * ******************************************************************************
 * This file is part of the GraphQL Bundle package.
 *
 * (c) YnloUltratech <support@ynloultratech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  *****************************************************************************
 */

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\OrderBy\Post;

use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\OrderBy\OrderByContext;
use Ynlo\GraphQLBundle\OrderBy\OrderByInterface;

class OrderByUser implements OrderByInterface
{
    public function __invoke(OrderByContext $context, $qb, $alias, OrderBy $orderBy)
    {
        // TODO: Implement __invoke() method.
    }
}