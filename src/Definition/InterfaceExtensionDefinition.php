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
use Ynlo\GraphQLBundle\Definition\Traits\ExtensionsAwareTrait;

/**
 * InterfaceExtensionDefinition
 */
class InterfaceExtensionDefinition implements ClassAwareDefinitionInterface, HasExtensionsInterface
{
    use ClassAwareDefinitionTrait;
    use ExtensionsAwareTrait;

    /**
     * @var int
     */
    protected $priority;

    /**
     * InterfaceExtensionDefinition constructor.
     *
     * @param string $class
     * @param int    $priority
     */
    public function __construct($class, $priority = 0)
    {
        $this->class = $class;
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }
}