<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\AnnotationParserInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Resolve and load definitions based on common annotations
 */
class AnnotationLoader implements DefinitionLoaderInterface
{
    /**
     * Folders inside bundles to locate definitions
     * TODO: allow add additional mapping on bundle config
     */
    private const DEFINITIONS_LOCATIONS = [
        'Model', //non persistent models like interfaces, or abstract classes
        'Entity', //doctrine entities
        'Mutation', //custom actions
        'Subscription', //custom subscriptions
        'Query', //custom actions
    ];

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var iterable|array|AnnotationParserInterface[]
     */
    protected $annotationParsers = [];

    /**
     * @param KernelInterface                      $kernel
     * @param Reader                               $reader
     * @param iterable|AnnotationParserInterface[] $annotationParsers
     */
    public function __construct(KernelInterface $kernel, Reader $reader, $annotationParsers = [])
    {
        $this->kernel = $kernel;
        $this->reader = $reader;
        $this->annotationParsers = $annotationParsers;
    }

    /**
     * {@inheritdoc}
     */
    public function loadDefinitions(Endpoint $endpoint): void
    {
        $classesToLoad = $this->resolveClasses();
        $annotationsMapping = [];
        foreach ($classesToLoad as $class) {
            $refClass = new \ReflectionClass($class);
            $annotations = $this->reader->getClassAnnotations($refClass);
            if ($annotations) {
                $annotationsMapping[$refClass->getName()] = [$refClass, $annotations];
            }
        }

        foreach ($this->annotationParsers as $parser) {
            if ($parser instanceof AnnotationParserInterface) {
                foreach ($annotationsMapping as $className => $map) {
                    [$refClass, $annotations] = $map;
                    foreach ($annotations as $annotation) {
                        if ($parser->supports($annotation)) {
                            $parser->parse($annotation, $refClass, $endpoint);
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function resolveClasses(): array
    {
        $bundles = $this->kernel->getBundles();
        $classes = [[]];
        foreach (self::DEFINITIONS_LOCATIONS as $definitionLocation) {
            foreach ($bundles as $bundle) {
                $path = $bundle->getPath().'/'.$definitionLocation;
                if (file_exists($path)) {
                    $classes[] = $this->extractNamespaceClasses($path, $bundle->getNamespace(), $definitionLocation);
                }
            }

            $path = $this->kernel->getProjectDir().'/src/'.$definitionLocation;
            if (file_exists($path)) {
                $classes[] = $this->extractNamespaceClasses($path, (new \ReflectionClass($this->kernel))->getNamespaceName(), $definitionLocation);
            }
        }

        $classes = array_merge(...$classes);

        return array_unique($classes);
    }

    /**
     * @param string $path
     * @param string $baseNamespace
     * @param string $baseLocation
     *
     * @return array
     */
    protected function extractNamespaceClasses($path, $baseNamespace, $baseLocation)
    {
        $classes = [];
        $finder = new Finder();
        foreach ($finder->in($path)->name('/.php$/')->getIterator() as $file) {
            $className = preg_replace('/.php$/', null, $file->getFilename());
            if ($file->getRelativePath()) {
                $subNamespace = str_replace('/', '\\', $file->getRelativePath());
                $fullyClassName = $baseNamespace.'\\'.$baseLocation.'\\'.$subNamespace.'\\'.$className;
            } else {
                $fullyClassName = $baseNamespace.'\\'.$baseLocation.'\\'.$className;
            }
            if (class_exists($fullyClassName) || interface_exists($fullyClassName)) {
                $classes[] = $fullyClassName;
            }
        }

        return $classes;
    }
}
