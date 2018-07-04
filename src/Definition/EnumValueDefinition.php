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

use Ynlo\GraphQLBundle\Definition\Traits\DefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\DeprecateTrait;

/**
 * EnumValueDefinition
 */
class EnumValueDefinition implements
    DefinitionInterface,
    DeprecateInterface
{
    use DefinitionTrait;
    use DeprecateTrait;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * EnumValueDefinition constructor.
     *
     * @param string $name
     * @param string $value
     * @param string $description
     */
    public function __construct(string $name = null, string $value = null, string $description = null)
    {
        $this->name = $name;
        $this->value = $value ?? $name;
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
