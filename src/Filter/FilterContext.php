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

/**
 * Context where the filter is applied
 */
class FilterContext
{
    /**
     * @var Endpoint
     */
    private $endpoint;

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
     * @param Endpoint                       $endpoint
     * @param FieldsAwareDefinitionInterface $node
     * @param null|FieldDefinition           $field
     */
    public function __construct(Endpoint $endpoint, FieldsAwareDefinitionInterface $node, ?FieldDefinition $field = null)
    {
        $this->endpoint = $endpoint;
        $this->node = $node;
        $this->field = $field;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        return $this->endpoint;
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
}
