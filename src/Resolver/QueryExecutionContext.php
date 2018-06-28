<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Resolver;

use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Context where a main query is executed
 */
class QueryExecutionContext
{
    /**
     * @var string
     */
    protected $queryId;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * QueryExecutionContext constructor.
     *
     * @param Endpoint $endpoint
     */
    public function __construct(Endpoint $endpoint)
    {
        $this->queryId = md5(time().mt_rand().spl_object_hash($this));
        $this->endpoint = $endpoint;
    }

    /**
     * @return string
     */
    public function getQueryId(): string
    {
        return $this->queryId;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        return $this->endpoint;
    }
}
