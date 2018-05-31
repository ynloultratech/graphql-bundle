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
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\Kernel;
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
        $this->registry->clearCache();
        $this->updateControlFile();
    }

    /**
     * warmUp the cache on request
     * NOTE: this behavior its switched in the YnloGraphQLExtension
     */
    public function warmUpOnEveryRequest()
    {
        if (!$this->isFreshCache()) {
            $this->warmUp(null);
            $this->updateControlFile();
        }
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

    protected function isFreshCache()
    {
        if (!file_exists($this->getControlFileName())) {
            return false;
        }

        $controlTime = filemtime($this->getControlFileName());

        $projectDir = $this->kernel->getProjectDir();

        if (Kernel::VERSION_ID >= 40000) {
            $dirs[] = $projectDir.'/config';
            $dirs[] = $projectDir.'/src';
        } else {
            $dirs[] = $this->kernel->getRootDir();
            $dirs[] = $this->kernel->getRootDir().'/../src';
        }

        $files = Finder::create()
                       ->in($dirs[1])
                       ->date(sprintf('>= %s', date('Y-m-d H:i:s', $controlTime)))
                       ->files();

        //exist at least one modified file
        foreach ($files as $file) {
            return false;
            break;
        }

        return true;
    }

    protected function getControlFileName()
    {
        return $this->kernel->getCacheDir().'/graphal.schema.timestamp';
    }

    protected function updateControlFile()
    {
        file_put_contents($this->getControlFileName(), time());
    }
}
