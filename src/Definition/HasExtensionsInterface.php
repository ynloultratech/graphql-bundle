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

/**
 * The definition can be managed using extensions
 */
interface HasExtensionsInterface
{
    /**
     * @return InterfaceExtensionDefinition[]
     */
    public function getExtensions(): array;

    /**
     * @param string $class
     */
    public function addExtension($class);
}
