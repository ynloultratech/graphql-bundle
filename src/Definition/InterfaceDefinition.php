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
use Ynlo\GraphQLBundle\Definition\Traits\ExtensionsAwareTrait;
use Ynlo\GraphQLBundle\Definition\Traits\FieldsAwareDefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\ObjectDefinitionTrait;

/**
 * Class InterfaceDefinition
 */
class InterfaceDefinition implements ObjectDefinitionInterface, HasExtensionsInterface
{
    use DefinitionTrait;
    use FieldsAwareDefinitionTrait;
    use ClassAwareDefinitionTrait;
    use ObjectDefinitionTrait;
    use ExtensionsAwareTrait;

    /**
     * @var string[]
     */
    protected $implementors = [];

    /**
     * @return \string[]
     */
    public function getImplementors(): array
    {
        return $this->implementors;
    }

    /**
     * @param string $type
     */
    public function addImplementor($type)
    {
        $this->implementors[$type] = $type;
    }
}
