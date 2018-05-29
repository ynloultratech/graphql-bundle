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
 * ExtensionManager
 */
class ExtensionManager
{
    /**
     * @var ExtensionInterface[]
     */
    protected $extensions;

    /**
     * @param iterable $extensions
     */
    public function __construct(iterable $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @return iterable|ExtensionInterface[]
     */
    public function getExtensions(): iterable
    {
        return $this->extensions;
    }
}
