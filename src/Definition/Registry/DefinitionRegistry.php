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

use Doctrine\Common\Util\Inflector;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Loader\DefinitionLoaderInterface;
use Ynlo\GraphQLBundle\Definition\MetaAwareInterface;
use Ynlo\GraphQLBundle\Definition\Plugin\DefinitionPluginInterface;
use Ynlo\GraphQLBundle\Extension\EndpointNotValidException;

/**
 * DefinitionRegistry
 */
class DefinitionRegistry
{
    /**
     * This endpoint is used as default endpoint
     */
    public const DEFAULT_ENDPOINT = 'default';

    /**
     * @var iterable|DefinitionLoaderInterface[]
     */
    private $loaders;

    /**
     * @var iterable|DefinitionPluginInterface[]
     */
    private $plugins;

    /**
     * @var array|Endpoint[]
     */
    private static $endpoints = [];

    /**
     * @var array
     */
    protected $endpointsConfig = [];

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * DefinitionRegistry constructor.
     *
     * @param iterable|DefinitionLoaderInterface[] $loaders
     * @param iterable|DefinitionPluginInterface[] $plugins
     * @param null|string                          $cacheDir
     * @param array                                $endpointsConfig
     */
    public function __construct(iterable $loaders, iterable $plugins, ?string $cacheDir = null, array $endpointsConfig = [])
    {
        $this->loaders = $loaders;
        $this->plugins = $plugins;
        $this->cacheDir = $cacheDir;
        $this->endpointsConfig = array_merge($endpointsConfig['endpoints'] ?? [], [self::DEFAULT_ENDPOINT => []]);
    }

    /**
     * Get endpoint schema
     *
     * For internal use can get the default endpoint (contains all definitions)
     * For consumers must get the endpoint for current consumer.
     *
     * @param string $name
     *
     * @return Endpoint
     *
     * @throws EndpointNotValidException
     */
    public function getEndpoint($name = self::DEFAULT_ENDPOINT): Endpoint
    {
        $endpoints = $this->endpointsConfig;
        unset($endpoints[self::DEFAULT_ENDPOINT]);
        $endpointsNames = array_keys($endpoints);
        if (self::DEFAULT_ENDPOINT !== $name && !\in_array($name, $endpointsNames)) {
            throw new EndpointNotValidException(
                sprintf(
                    '"%s" is not a valid configured endpoint, use one of the following endpoints: [%s]',
                    $name,
                    implode($endpointsNames, ',')
                )
            );
        }

        //use first static cache
        if (isset(self::$endpoints[$name])) {
            return self::$endpoints[$name];
        }

        //use file cache
        self::$endpoints[$name] = $this->loadCache($name);

        //retry after load from file
        if (isset(self::$endpoints[$name]) && self::$endpoints[$name] instanceof Endpoint) {
            return self::$endpoints[$name];
        }

        $this->initialize($name);

        return self::$endpoints[$name];
    }

    /**
     * remove the specification cache
     */
    public function clearCache()
    {
        @unlink($this->cacheFileName('default.raw'));
        foreach ($this->endpointsConfig as $name => $config) {
            unset(self::$endpoints[$name]);
            @unlink($this->cacheFileName($name));
            $this->initialize($name);
        }
    }

    /**
     * Initialize endpoint
     *
     * @param string $name
     */
    protected function initialize(string $name)
    {
        $rawDefault = $this->loadCache('default.raw');
        if (!$rawDefault) {
            $rawDefault = new Endpoint(self::DEFAULT_ENDPOINT);

            foreach ($this->loaders as $loader) {
                $loader->loadDefinitions($rawDefault);
            }

            $this->saveCache('default.raw', $rawDefault);
        }

        if (!isset(self::$endpoints[$name])) {
            //copy from raw to specific endpoint
            self::$endpoints[$name] = new Endpoint($name);
            self::$endpoints[$name]->setTypes($rawDefault->allTypes());
            self::$endpoints[$name]->setMutations($rawDefault->allMutations());
            self::$endpoints[$name]->setQueries($rawDefault->allQueries());

            $this->compile(self::$endpoints[$name]);
        }

        $this->saveCache($name, self::$endpoints[$name]);
    }

    protected function cacheFileName($name): string
    {
        return sprintf('%s%sgraphql.registry_definitions_%s.meta', $this->cacheDir, DIRECTORY_SEPARATOR, Inflector::tableize($name));
    }

    protected function loadCache($name): ?Endpoint
    {
        if (file_exists($this->cacheFileName($name))) {
            $content = @file_get_contents($this->cacheFileName($name));
            if ($content) {
                return unserialize($content, ['allowed_classes' => true]);
            }
        }

        return null;
    }

    protected function saveCache($name, Endpoint $endpoint): void
    {
        file_put_contents($this->cacheFileName($name), serialize($endpoint));
    }

    /**
     * Verify endpoint definitions and do some tasks to prepare the endpoint
     *
     * @param Endpoint $endpoint
     */
    protected function compile(Endpoint $endpoint): void
    {
        //run all extensions for each definition
        foreach ($this->plugins as $plugin) {
            //run extensions recursively in all types and fields
            foreach ($endpoint->allTypes() as $type) {
                $this->configureDefinition($plugin, $type, $endpoint);
                if ($type instanceof FieldsAwareDefinitionInterface) {
                    foreach ($type->getFields() as $field) {
                        $this->configureDefinition($plugin, $field, $endpoint);
                        foreach ($field->getArguments() as $argument) {
                            $this->configureDefinition($plugin, $argument, $endpoint);
                        }
                    }
                }
            }

            //run extension in all queries
            foreach ($endpoint->allQueries() as $query) {
                $this->configureDefinition($plugin, $query, $endpoint);
                foreach ($query->getArguments() as $argument) {
                    $this->configureDefinition($plugin, $argument, $endpoint);
                }
            }

            //run extensions in all mutations
            foreach ($endpoint->allMutations() as $mutation) {
                $this->configureDefinition($plugin, $mutation, $endpoint);
                foreach ($mutation->getArguments() as $argument) {
                    $this->configureDefinition($plugin, $argument, $endpoint);
                }
            }

            $plugin->configureEndpoint($endpoint);
        }
    }

    /**
     * @param DefinitionPluginInterface $plugin
     * @param DefinitionInterface       $definition
     * @param Endpoint                  $endpoint
     */
    protected function configureDefinition(DefinitionPluginInterface $plugin, DefinitionInterface $definition, Endpoint $endpoint)
    {
        $config = [];
        if ($definition instanceof MetaAwareInterface) {
            $treeBuilder = new TreeBuilder();
            /** @var NodeBuilder $root */
            $root = $treeBuilder->root($plugin->getName());
            $plugin->buildConfig($root);

            if ($definition->hasMeta($plugin->getName())) {
                $options = $definition->getMeta($plugin->getName());
                $processor = new Processor();

                try {
                    $options = $plugin->normalizeConfig($definition, $options);
                    $config = $processor->process($treeBuilder->buildTree(), [$options]);
                } catch (InvalidConfigurationException $exception) {
                    $error = sprintf('Error compiling schema definition "%s", %s', $definition->getName(), $exception->getMessage());
                    throw new \RuntimeException($error, 0, $exception);
                }
            }
        }
        $plugin->configure($definition, $endpoint, $config);
    }
}
