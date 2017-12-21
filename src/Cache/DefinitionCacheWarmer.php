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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\KernelEvents;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;

/**
 * DefinitionCacheWarmer
 */
class DefinitionCacheWarmer extends CacheWarmer implements EventSubscriberInterface
{
    /**
     * @var DefinitionRegistry
     */
    protected $registry;

    /**
     * DefinitionCacheWarmer constructor.
     *
     * @param DefinitionRegistry $registry
     */
    public function __construct(DefinitionRegistry $registry)
    {
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
        $this->registry->clearCache();
    }

    /**
     * warmUp the cache on request
     * NOTE: this behavior its switched in the YnloGraphQLExtension
     */
    public function warmUpOnEveryRequest()
    {
        $this->warmUp(null);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'warmUpOnEveryRequest',
        ];
    }
}
