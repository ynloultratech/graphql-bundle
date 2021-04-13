<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\Kernel;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;

/**
 * DefinitionCacheWarmer
 */
class DefinitionCacheWarmer extends CacheWarmer
{
    /**
     * @var DefinitionRegistry
     */
    protected $registry;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * DefinitionCacheWarmer constructor.
     *
     * @param Kernel             $kernel
     * @param DefinitionRegistry $registry
     */
    public function __construct(Kernel $kernel, DefinitionRegistry $registry)
    {
        $this->kernel = $kernel;
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function warmUp($cacheDir)
    {
        $this->registry->clearCache(true);
    }
}
