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

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Query\Node\Node;

/**
 * @GraphQL\Query()
 * @GraphQL\Argument(name="login", type="string!", internalName="id")
 */
class User extends Node
{
    protected $fetchBy = 'username';
}
