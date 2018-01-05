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
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;

class TypeAutoLoader
{
    protected static $loaded = false;

    private $cacheDir;
    private $bundles;

    public function __construct(string $cacheDir, array $bundles)
    {
        $this->cacheDir = $cacheDir;
        $this->bundles = $bundles;
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

        if ($this->loadFromCacheCache()) {
            self::$loaded = true;

            return;
        }

        self::$loaded = true;

        foreach ($this->bundles as $bundle) {
            $path = $bundle['path'].'/Type';
            if (file_exists($path)) {
                $finder = new Finder();
                foreach ($finder->in($path)->name('/Type.php$/')->getIterator() as $file) {
                    $namespace = $bundle['namespace'];
                    $className = preg_replace('/.php$/', null, $file->getFilename());
                    $name = preg_replace('/Type$/', null, $className);

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
                            TypeRegistry::addTypeMapping($name, $fullyClassName);
                        }
                    }
                }
            }
        }

        $this->saveCache();
    }

    /**
     * @return string
     */
    protected function cacheFileName()
    {
        return $this->cacheDir.DIRECTORY_SEPARATOR.'graphql.type_map.meta';
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
