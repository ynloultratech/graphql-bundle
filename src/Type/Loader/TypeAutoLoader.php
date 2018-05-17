<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type\Loader;

use GraphQL\Type\Definition\Type;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;

class TypeAutoLoader
{
    protected static $loaded = false;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Autoload all registered types
     */
    public function autoloadTypes()
    {
        //loaded and static
        if (self::$loaded) {
            return;
        }

        if (!$this->kernel->isDebug() && $this->loadFromCacheCache()) {
            self::$loaded = true;

            return;
        }

        self::$loaded = true;

        foreach ($this->kernel->getBundles() as $bundle) {
            $path = $bundle->getPath().'/Type';
            if (file_exists($path)) {
                $this->registerBundleTypes($path, $bundle->getNamespace());
            }
        }

        if (Kernel::VERSION_ID >= 40000) {
            $path = $this->kernel->getRootDir().'/Type';
            if (file_exists($path)) {
                $this->registerBundleTypes($path, 'App');
            }
        }

        $this->saveCache();
    }

    /**
     * Register all GraphQL types for given path
     *
     * @param string $path
     * @param string $namespace
     *
     * @throws \ReflectionException
     */
    protected function registerBundleTypes($path, $namespace)
    {
        $finder = new Finder();
        foreach ($finder->in($path)->name('/Type.php$/')->getIterator() as $file) {
            $className = preg_replace('/.php$/', null, $file->getFilename());

            if ($file->getRelativePath()) {
                $subNamespace = str_replace('/', '\\', $file->getRelativePath());
                $fullyClassName = $namespace.'\\Type\\'.$subNamespace.'\\'.$className;
            } else {
                $fullyClassName = $namespace.'\\Type\\'.$className;
            }

            if (class_exists($fullyClassName)) {
                $ref = new \ReflectionClass($fullyClassName);
                if ($ref->isSubclassOf(Type::class)
                    && $ref->isInstantiable()
                    && !$ref->getConstructor()->getNumberOfRequiredParameters()
                ) {
                    /** @var Type $instance */
                    $instance = $ref->newInstance();
                    TypeRegistry::addTypeMapping($instance->name, $fullyClassName);
                }
            }
        }
    }

    /**
     * @return string
     */
    protected function cacheFileName()
    {
        return $this->kernel->getCacheDir().DIRECTORY_SEPARATOR.'graphql.type_map.meta';
    }

    /**
     * Load cache
     *
     * @return bool on success
     */
    protected function loadFromCacheCache(): bool
    {
        if (file_exists($this->cacheFileName())) {
            $content = @file_get_contents($this->cacheFileName());
            if ($content) {
                TypeRegistry::setTypeMapping(unserialize($content));

                return true;
            }
        }

        return false;
    }

    /**
     * Save cache
     */
    protected function saveCache()
    {
        file_put_contents($this->cacheFileName(), serialize(TypeRegistry::getTypeMapp()));
    }
}
