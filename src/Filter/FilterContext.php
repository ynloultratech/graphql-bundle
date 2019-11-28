<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter;

use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;

/**
 * Context where the filter is applied
 */
class FilterContext
{
    /**
     * @var ResolverContext
     */
    private $parentContext;

    /**
     * @var FieldsAwareDefinitionInterface
     */
    private $node;

    /**
     * @var FieldDefinition|null
     */
    private $field;

    /**
     * FilterContext constructor.
     *
     * @param ResolverContext                $parentContext
     * @param FieldsAwareDefinitionInterface $node
     * @param null|FieldDefinition           $field
     */
    public function __construct(ResolverContext $parentContext, FieldsAwareDefinitionInterface $node, ?FieldDefinition $field = null)
    {
        $this->parentContext = $parentContext;
        $this->node = $node;
        $this->field = $field;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        return $this->parentContext->getEndpoint();
    }

    /**
     * @return FieldsAwareDefinitionInterface
     */
    public function getNode(): FieldsAwareDefinitionInterface
    {
        return $this->node;
    }

    /**
     * @return null|FieldDefinition
     */
    public function getField(): ?FieldDefinition
    {
        return $this->field;
    }

    /**
     * @param null|FieldDefinition $field
     */
    public function setField(?FieldDefinition $field): void
    {
        $this->field = $field;
    }

    /**
     * @return ResolverContext
     */
    public function getParentContext(): ResolverContext
    {
        return $this->parentContext;
    }
}
