<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition;

use Ynlo\GraphQLBundle\Definition\Traits\ClassAwareDefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\DefinitionTrait;

/**
 * UnionDefinition
 */
class UnionDefinition implements
    DefinitionInterface,
    ClassAwareDefinitionInterface
{
    use DefinitionTrait;
    use ClassAwareDefinitionTrait;

    /**
     * @var UnionTypeDefinition[]
     */
    protected $types = [];

    /**
     * @var string
     */
    protected $resolver;

    /**
     * @return UnionTypeDefinition[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param UnionTypeDefinition $type
     */
    public function addType(UnionTypeDefinition $type)
    {
        $this->types[$type->getType()] = $type;
    }

    /**
     * @param string $type
     */
    public function removeType(string $type)
    {
        unset($this->types[$type]);
    }

    /**
     * @return string
     */
    public function getResolver(): string
    {
        return $this->resolver;
    }

    /**
     * @param string $resolver
     *
     * @return UnionDefinition
     */
    public function setResolver(string $resolver): UnionDefinition
    {
        $this->resolver = $resolver;

        return $this;
    }
}
