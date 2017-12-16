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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Finder\Finder;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;

/**
 * Class TypeAutoLoader
 */
class TypeAutoLoader implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected static $loaded = false;

    /**
     * Autoload all registered types
     */
    public function autoloadTypes()
    {
        if (self::$loaded) {
            return;
        }

        self::$loaded = true;
        $bundles = $this->container->get('kernel')->getBundles();

        //TODO: save in cache for production

        foreach ($bundles as $bundle) {
            $path = $bundle->getPath().'/Type';
            if (file_exists($path)) {
                $finder = new Finder();
                foreach ($finder->in($path)->name('/Type.php$/')->getIterator() as $file) {
                    $namespace = $bundle->getNamespace();
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
    }
}
