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

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;
use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServices;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\AnnotationParserInterface;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionManager;

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
        'Query', //custom actions
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
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->reader = $container->get('annotations.reader');
    }

    /**
     * {@inheritdoc}
     */
    public function loadDefinitions(DefinitionManager $definitionManager): void
    {
        /** @var Definition $resolversServiceDefinition */
        $resolverDefinitions = $this->container
            ->get(TaggedServices::class)
            ->findTaggedServices('graphql.definition_resolver');

        $resolvers = [];
        foreach ($resolverDefinitions as $resolverDefinition) {
            $attr = $resolverDefinition->getAttributes();
            $priority = 0;
            if (isset($attr['priority'])) {
                $priority = $attr['priority'];
            }

            $resolvers[] = [$priority, $resolverDefinition->getService()];
        }

        //sort by priority
        usort(
            $resolvers,
            function ($service1, $service2) {
                list($priority1) = $service1;
                list($priority2) = $service2;

                return version_compare($priority2, $priority1);
            }
        );

        $classesToLoad = $this->resolveClasses();
        foreach ($resolvers as $resolver) {
            list(, $resolver) = $resolver;
            if ($resolver instanceof AnnotationParserInterface) {
                foreach ($classesToLoad as $class) {
                    $refClass = new \ReflectionClass($class);
                    $annotations = $this->reader->getClassAnnotations($refClass);
                    foreach ($annotations as $annotation) {
                        if ($resolver->supports($annotation)) {
                            $resolver->parse($annotation, $refClass, $definitionManager);
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
                            $classes[] = $fullyClassName;
                        }
                    }
                }
            }
        }

        return array_unique($classes);
    }
}
