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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Contracts\Cache\CacheInterface;
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

    private CacheInterface $cache;

    /**
     * DefinitionRegistry constructor.
     *
     * Configured endpoints array should have the following format:
     *
     * [
     * 'endpoints' => [
     *   'name' => [
     *      'roles'=> [],
     *      'host' => '',
     *      'path' => ''
     *    ]
     *  ]
     * ]
     *
     * @param CacheInterface                       $cache
     * @param iterable|DefinitionLoaderInterface[] $loaders
     * @param iterable|DefinitionPluginInterface[] $plugins         *
     * @param array                                $endpointsConfig array of configured endpoints
     *
     */
    public function __construct(CacheInterface $cache, iterable $loaders = [], iterable $plugins = [], array $endpointsConfig = [])
    {
        $this->loaders = $loaders;
        $this->plugins = $plugins;
        $this->cache = $cache;
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
            throw new EndpointNotValidException($name, $endpointsNames);
        }

        //use first static cache
        if (isset(self::$endpoints[$name])) {
            return self::$endpoints[$name];
        }

        self::$endpoints[$name] = $this->initialize($name);

        return self::$endpoints[$name];
    }

    /**
     * remove the specification cache
     *
     * @param bool $warmUp recreate the cache after clear
     */
    public function clearCache($warmUp = false)
    {
        $this->cache->delete('default.raw');
        foreach ($this->endpointsConfig as $name => $config) {
            unset(self::$endpoints[$name]);
            $this->cache->delete($name);
            if ($warmUp) {
                $this->initialize($name);
            }
        }
    }

    /**
     * Initialize endpoint
     *
     * @param string $name
     */
    protected function initialize(string $name): Endpoint
    {
        $rawDefault = $this->cache->get(
            'default.raw',
            function () {
                $rawDefault = new Endpoint(self::DEFAULT_ENDPOINT);

                foreach ($this->loaders as $loader) {
                    $loader->loadDefinitions($rawDefault);
                }

                return $rawDefault;
            }
        );

        return $this->cache->get(
            $name,
            function () use ($name, $rawDefault) {
                $endpoint = new Endpoint($name);
                $endpoint->setTypes($rawDefault->allTypes());
                $endpoint->setMutations($rawDefault->allMutations());
                $endpoint->setQueries($rawDefault->allQueries());
                $endpoint->setSubscriptions($rawDefault->allSubscriptions());

                $this->compile($endpoint);

                return $endpoint;
            }
        );
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

            //run extensions in all subscriptions
            foreach ($endpoint->allSubscriptions() as $subscription) {
                $this->configureDefinition($plugin, $subscription, $endpoint);
                foreach ($subscription->getArguments() as $argument) {
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
            $treeBuilder = new TreeBuilder($plugin->getName());
            $root = $treeBuilder->getRootNode();
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
