<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Ynlo\GraphQLBundle\Annotation\AddNode;
use Ynlo\GraphQLBundle\Annotation\DeleteNode;
use Ynlo\GraphQLBundle\Annotation\GetNode;
use Ynlo\GraphQLBundle\Annotation\InputObjectType;
use Ynlo\GraphQLBundle\Annotation\InterfaceType;
use Ynlo\GraphQLBundle\Annotation\ListNodes;
use Ynlo\GraphQLBundle\Annotation\Mutation;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Annotation\Query;
use Ynlo\GraphQLBundle\Annotation\UpdateNode;
use Ynlo\GraphQLBundle\DefinitionLoader\AnnotationDefinitionExtractor\ActionExtractor;
use Ynlo\GraphQLBundle\DefinitionLoader\AnnotationDefinitionExtractor\AnnotationDefinitionExtractorInterface;
use Ynlo\GraphQLBundle\DefinitionLoader\AnnotationDefinitionExtractor\CRUDExtractor;
use Ynlo\GraphQLBundle\DefinitionLoader\AnnotationDefinitionExtractor\ObjectExtractor;

/**
 * Class DefinitionAutoLoader
 */
class DefinitionAutoLoader implements DefinitionLoaderInterface
{
    /**
     * Folders inside bundles to locate definitions
     */
    private const DEFINITIONS_LOCATIONS = [
        'Field', //contains custom input types
        'Model', //non persistent models like interfaces, or abstract classes
        'Entity', //doctrine entities
        'Action', //custom actions
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * @param ContainerInterface $container
     * @param AnnotationReader   $reader
     */
    public function __construct(ContainerInterface $container, AnnotationReader $reader)
    {
        $this->container = $container;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadDefinitions(DefinitionManager $definitionManager): void
    {
        $extractors = [
            new ObjectExtractor(),
            new ActionExtractor(),
            new CRUDExtractor(),
        ];

        $classesToLoad = $this->resolveClasses();

        /** @var AnnotationDefinitionExtractorInterface[] $extractors */
        foreach ($extractors as $extractor) {
            foreach ($classesToLoad as $class) {
                $refClass = new \ReflectionClass($class);
                $annotations = $this->reader->getClassAnnotations($refClass);
                foreach ($annotations as $annotation) {
                    if ($extractor->supports($annotation)) {
                        $extractor->setReader($this->reader);
                        $extractor->extract($annotation, $refClass, $definitionManager);
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
        $bundles = $this->container->get('kernel')->getBundles();
        $classes = [];
        foreach (self::DEFINITIONS_LOCATIONS as $definitionLocation) {
            foreach ($bundles as $bundle) {
                $path = $bundle->getPath().'/'.$definitionLocation;
                if (file_exists($path)) {
                    $finder = new Finder();
                    foreach ($finder->in($path)->name('/.php$/')->getIterator() as $file) {
                        $namespace = $bundle->getNamespace();
                        $className = preg_replace('/.php$/', null, $file->getFilename());

                        if ($file->getRelativePath()) {
                            $subNamespace = str_replace('/', '\\', $file->getRelativePath());
                            $fullyClassName = $namespace.'\\'.$definitionLocation.'\\'.$subNamespace.'\\'.$className;
                        } else {
                            $fullyClassName = $namespace.'\\'.$definitionLocation.'\\'.$className;
                        }
                        if (class_exists($fullyClassName) || interface_exists($fullyClassName)) {
                            $ref = new \ReflectionClass($fullyClassName);

                            $annotations = $this->reader->getClassAnnotations($ref);
                            foreach ($annotations as $annotation) {
                                if ($annotation instanceof InputObjectType
                                    || $annotation instanceof ObjectType
                                    || $annotation instanceof InterfaceType
                                    || $annotation instanceof Query
                                    || $annotation instanceof GetNode
                                    || $annotation instanceof ListNodes
                                    || $annotation instanceof Mutation
                                    || $annotation instanceof UpdateNode
                                    || $annotation instanceof AddNode
                                    || $annotation instanceof DeleteNode
                                ) {
                                    $classes[] = $fullyClassName;
                                }
                            }
                        }
                    }
                }
            }
        }

        return array_unique($classes);
    }
}
