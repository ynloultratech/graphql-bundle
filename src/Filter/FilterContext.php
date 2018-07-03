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
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
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
     * @var ObjectDefinition
     */
    private $node;

    /**
     * @var FieldDefinition|null
     */
    private $field;

    /**
     * FilterContext constructor.
     *
     * @param Endpoint             $endpoint
     * @param ObjectDefinition     $node
     * @param null|FieldDefinition $field
     */
    public function __construct(Endpoint $endpoint, ObjectDefinition $node, ?FieldDefinition $field = null)
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
     * @return ObjectDefinition
     */
    public function getNode(): ObjectDefinition
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
