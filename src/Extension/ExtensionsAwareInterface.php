<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Extension;

/**
 * Every resolver using this interface will be automatically injected
 * with all available interfaces to current node
 */
interface ExtensionsAwareInterface
{
    /**
     * @param ExtensionInterface[] $extensions
     */
    public function setExtensions($extensions);
}
