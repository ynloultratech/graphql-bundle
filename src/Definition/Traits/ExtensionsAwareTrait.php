<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Traits;

use Ynlo\GraphQLBundle\Definition\InterfaceExtensionDefinition;

/**
 * ExtensionsAwareTrait
 */
trait ExtensionsAwareTrait
{
    /**
     * @var string[]
     */
    protected $extensions = [];

    /**
     * @return InterfaceExtensionDefinition[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @param string $class
     * @param int    $priority
     */
    public function addExtension($class, $priority = 0)
    {
        $this->extensions[$class] = new InterfaceExtensionDefinition($class, $priority);
    }
}
