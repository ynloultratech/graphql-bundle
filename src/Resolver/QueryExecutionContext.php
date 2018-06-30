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

use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
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
     * @var ExecutableDefinitionInterface
     */
    protected $definition;

    /**
     * QueryExecutionContext constructor.
     *
     * @param Endpoint                      $endpoint
     * @param ExecutableDefinitionInterface $definition
     */
    public function __construct(Endpoint $endpoint, ExecutableDefinitionInterface $definition = null)
    {
        $this->queryId = md5(time().mt_rand().spl_object_hash($this));
        $this->endpoint = $endpoint;
        $this->definition = $definition ?? new QueryDefinition();
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

    /**
     * @return ExecutableDefinitionInterface
     */
    public function getDefinition(): ExecutableDefinitionInterface
    {
        return $this->definition;
    }
}
