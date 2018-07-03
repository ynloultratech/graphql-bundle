<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter\Resolver;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Annotation\Filter;
use Ynlo\GraphQLBundle\Definition\ImplementorInterface;
use Ynlo\GraphQLBundle\Definition\NodeAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\FilterResolverInterface;

/**
 * Resolve custom filters using naming convention.
 * Filters should me placed under Filter folder on each bundle or App namespace
 * using the following format:
 *
 * {BundleNamespace}\Filter\{Node}\{FilterName}
 *
 * > Filters must implements FilterInterface and has the Filter annotation
 */
class NamingConventionFilterResolver implements FilterResolverInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param KernelInterface $kernel
     * @param Reader          $reader
     */
    public function __construct(KernelInterface $kernel, Reader $reader)
    {
        $this->kernel = $kernel;
        $this->reader = $reader;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ObjectDefinitionInterface $node, Endpoint $endpoint): array
    {
        $types = [];
        if ($node instanceof ImplementorInterface && $node->getInterfaces()) {
            $types = array_merge($types, $node->getInterfaces());
        }
        if ($node instanceof NodeAwareDefinitionInterface && $node->getNode()) {
            $types[] = $node->getNode();
        }

        $paths = [];
        foreach ($types as $type) {
            //search for filters using naming convention inside Bundle/Filter/...
            foreach ($this->kernel->getBundles() as $bundle) {
                $path = "{$bundle->getPath()}/Filter/$type";
                if (file_exists($path)) {
                    $paths[$path] = "{$bundle->getNamespace()}\Filter\\$type";
                }
            }

            //symfony ^4.0
            if (Kernel::VERSION_ID >= 40000) {
                $path = "{$this->kernel->getRootDir()}/Filter/$type";
                if (file_exists($path)) {
                    $paths[$path] = "App\Filter\\$type";
                }
            }
        }

        $resolvedFilters = [];
        foreach ($paths as $path => $namespace) {
            $finder = new Finder();
            foreach ($finder->in($path)->name('/.php$/')->getIterator() as $file) {
                $className = preg_replace('/.php$/', null, $file->getFilename());
                if ($file->getRelativePath()) {
                    $subNamespace = str_replace('/', '\\', $file->getRelativePath());
                    $fullyClassName = $namespace.'\\'.$subNamespace.'\\'.$className;
                } else {
                    $fullyClassName = $namespace.'\\'.$className;
                }

                if (class_exists($fullyClassName)) {
                    /** @var Filter $filter */
                    $filter = $this->reader->getClassAnnotation(new \ReflectionClass($fullyClassName), Filter::class);
                    if ($filter) {
                        $filter->resolver = $fullyClassName;
                        if (!$filter->name) {
                            preg_match('/\w+$/', $fullyClassName, $matches);
                            $filter->name = lcfirst($matches[0]);
                        }
                        $resolvedFilters[] = $filter;
                    }
                }
            }
        }

        return $resolvedFilters;
    }
}
