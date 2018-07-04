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
 * EnumDefinition
 */
class EnumDefinition implements
    DefinitionInterface,
    ClassAwareDefinitionInterface
{
    use DefinitionTrait;
    use ClassAwareDefinitionTrait;

    /**
     * @var EnumValueDefinition[]
     */
    protected $values = [];

    /**
     * @return EnumValueDefinition[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param EnumValueDefinition $definition
     */
    public function addValue(EnumValueDefinition $definition)
    {
        $this->values[$definition->getName()] = $definition;
    }
}
