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
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;

class TypeAutoLoader implements CacheWarmerInterface
{
    protected KernelInterface $kernel;

    protected CacheInterface $cache;

    public function __construct(KernelInterface $kernel, CacheInterface $cache)
    {
        $this->kernel = $kernel;
        $this->cache = $cache;
    }

    public function warmUp(string $cacheDir)
    {
        $this->cache->delete('types');
        $this->autoloadTypes();
    }

    public function isOptional()
    {
        return false;
    }

    /**
     * Autoload all registered types
     */
    public function autoloadTypes()
    {
        $typeMap = $this->cache->get(
            'types',
            function () {
                foreach ($this->kernel->getBundles() as $bundle) {
                    $path = $bundle->getPath().'/Type';
                    if (file_exists($path)) {
                        $this->registerBundleTypes($path, $bundle->getNamespace());
                    }
                }

                $path = $this->kernel->getProjectDir().'/src/Type';
                if (file_exists($path)) {
                    $this->registerBundleTypes($path, 'App');
                }

                return TypeRegistry::getTypeMapp();
            }
        );

        TypeRegistry::setTypeMapping($typeMap);
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
                if ($ref->isSubclassOf(Type::class) && $ref->isInstantiable()) {
                    $requiredParams = false;
                    foreach ($ref->getConstructor()->getParameters() as $parameter) {
                        if (($type = $parameter->getType()) && is_a($type->getName(), DefinitionInterface::class, true)) {
                            continue 2;
                        }
                    }

                    if ($requiredParams) {
                        $error = sprintf('The graphql type defined in class "%s" is not instantiable because has some required parameters in the constructor.', $fullyClassName);
                        throw new \LogicException($error);
                    }
                    /** @var Type $instance */
                    $instance = $ref->newInstance();
                    TypeRegistry::addTypeMapping($instance->name, $fullyClassName);
                }
            }
        }
    }
}
