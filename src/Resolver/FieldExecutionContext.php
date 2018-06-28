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

use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;

/**
 * Context where a field is executed
 */
class FieldExecutionContext
{
    /**
     * @var QueryExecutionContext
     */
    private $queryContext;

    /**
     * @var FieldsAwareDefinitionInterface
     */
    private $definition;

    /**
     * FieldExecutionContext constructor.
     *
     * @param QueryExecutionContext          $queryContext
     * @param FieldsAwareDefinitionInterface $definition
     */
    public function __construct(QueryExecutionContext $queryContext, FieldsAwareDefinitionInterface $definition)
    {
        $this->queryContext = $queryContext;
        $this->definition = $definition;
    }

    /**
     * @return QueryExecutionContext
     */
    public function getQueryContext(): QueryExecutionContext
    {
        return $this->queryContext;
    }

    /**
     * @return FieldsAwareDefinitionInterface
     */
    public function getDefinition(): FieldsAwareDefinitionInterface
    {
        return $this->definition;
    }
}
