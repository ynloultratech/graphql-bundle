<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Events;

use Ynlo\GraphQLBundle\Request\ExecuteQuery;

class GraphQLOperationEvent
{
    /**
     * @var ExecuteQuery
     */
    protected $operation;

    /**
     * @var string
     */
    protected $endpoint;
}