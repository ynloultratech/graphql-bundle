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

use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Request\ExecuteQuery;

class GraphQLOperationEvent extends GraphQLEventProxy
{
    /**
     * @var ExecuteQuery
     */
    protected $operation;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * GraphQLOperationEvent constructor.
     *
     * @param ExecuteQuery $operation
     * @param Endpoint     $endpoint
     */
    public function __construct(ExecuteQuery $operation, Endpoint $endpoint)
    {
        $this->operation = $operation;
        $this->endpoint = $endpoint;
    }

    /**
     * @return ExecuteQuery
     */
    public function getOperation(): ExecuteQuery
    {
        return $this->operation;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        return $this->endpoint;
    }
}
