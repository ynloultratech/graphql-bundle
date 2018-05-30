<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Registry;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServices;
use Ynlo\GraphQLBundle\Component\TaggedServices\TagSpecification;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Extension\DefinitionExtensionInterface;
use Ynlo\GraphQLBundle\Definition\Extension\DefinitionExtensionManager;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Loader\DefinitionLoaderInterface;
use Ynlo\GraphQLBundle\Definition\MetaAwareInterface;

/**
 * DefinitionRegistry
 */
class DefinitionRegistry
{
    /**
     * @var TaggedServices
     */
    private $taggedServices;

    /**
     * @var DefinitionExtensionManager
     */
    private $extensionManager;

    /**
     * @var Endpoint
     */
    private static $endpoint;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * DefinitionRegistry constructor.
     *
     * @param TaggedServices             $taggedServices
     * @param DefinitionExtensionManager $extensionManager
     * @param null|string                $cacheDir
     */
    public function __construct(TaggedServices $taggedServices, DefinitionExtensionManager $extensionManager, ?string $cacheDir = null)
    {
        $this->taggedServices = $taggedServices;
        $this->extensionManager = $extensionManager;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        //use first static cache
        if (self::$endpoint) {
            return self::$endpoint;
        }

        $this->loadCache();

        //use file cache
        if (self::$endpoint) {
            return self::$endpoint;
        }

        $this->initialize();

        return self::$endpoint;
    }

    /**
     * remove the specification cache
     */
    public function clearCache()
    {
        @unlink($this->cacheFileName());
        $this->initialize();
    }

    /**
     * Initialize endpoint
     */
    protected function initialize()
    {
        self::$endpoint = new Endpoint();

        $specifications = $this->getTaggedServices('graphql.definition_loader');
        foreach ($specifications as $specification) {
            $resolver = $specification->getService();
            if ($resolver instanceof DefinitionLoaderInterface) {
                $resolver->loadDefinitions(self::$endpoint);
            }
        }

        $this->compile(self::$endpoint);
        $this->saveCache();
    }

    protected function cacheFileName(): string
    {
        return $this->cacheDir.DIRECTORY_SEPARATOR.'graphql.registry_definitions.meta';
    }

    protected function loadCache(): void
    {
        if (file_exists($this->cacheFileName())) {
            $content = @file_get_contents($this->cacheFileName());
            if ($content) {
                self::$endpoint = unserialize($content, ['allowed_classes' => true]);
            }
        }
    }

    protected function saveCache(): void
    {
        file_put_contents($this->cacheFileName(), serialize(self::$endpoint));
    }

    /**
     * Verify endpoint definitions and do some tasks to prepare the endpoint
     *
     * @param Endpoint $endpoint
     */
    protected function compile(Endpoint $endpoint): void
    {
        //run all extensions for each definition
        foreach ($this->extensionManager->getExtensions() as $extension) {
            //run extensions recursively in all types and fields
            foreach ($endpoint->allTypes() as $type) {
                $this->configureDefinition($extension, $type, $endpoint);
                if ($type instanceof FieldsAwareDefinitionInterface) {
                    foreach ($type->getFields() as $field) {
                        $this->configureDefinition($extension, $field, $endpoint);
                        foreach ($field->getArguments() as $argument) {
                            $this->configureDefinition($extension, $argument, $endpoint);
                        }
                    }
                }
            }

            //run extension in all queries
            foreach ($endpoint->allQueries() as $query) {
                $this->configureDefinition($extension, $query, $endpoint);
                foreach ($query->getArguments() as $argument) {
                    $this->configureDefinition($extension, $argument, $endpoint);
                }
            }

            //run extensions in all mutations
            foreach ($endpoint->allMutations() as $mutation) {
                $this->configureDefinition($extension, $mutation, $endpoint);
                foreach ($mutation->getArguments() as $argument) {
                    $this->configureDefinition($extension, $argument, $endpoint);
                }
            }

            $extension->configureEndpoint($endpoint);
        }
    }

    /**
     * @param DefinitionExtensionInterface $extension
     * @param DefinitionInterface          $definition
     * @param Endpoint                     $endpoint
     */
    protected function configureDefinition(DefinitionExtensionInterface $extension, DefinitionInterface $definition, Endpoint $endpoint)
    {
        $config = [];
        if ($definition instanceof MetaAwareInterface) {
            $treeBuilder = new TreeBuilder();
            /** @var NodeBuilder $root */
            $root = $treeBuilder->root($extension->getName());
            $extension->buildConfig($root);

            if ($definition->hasMeta($extension->getName())) {
                $options = $definition->getMeta($extension->getName());
                $processor = new Processor();

                try {
                    $options = $extension->normalizeConfig($definition, $options);
                    $config = $processor->process($treeBuilder->buildTree(), [$options]);
                } catch (InvalidConfigurationException $exception) {
                    $error = sprintf('Error compiling schema definition "%s", %s', $definition->getName(), $exception->getMessage());
                    throw new \RuntimeException($error, 0, $exception);
                }
            }
        }
        $extension->configure($definition, $endpoint, $config);
    }


    /**
     * @param string $tag
     *
     * @return array|TagSpecification[]
     */
    private function getTaggedServices($tag): array
    {
        return $this->taggedServices->findTaggedServices($tag);
    }
}
