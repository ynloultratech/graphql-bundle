<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Plugin;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\NodeAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Definition\SubscriptionDefinition;
use Ynlo\GraphQLBundle\Resolver\EmptyObjectResolver;

/**
 * This extension configure namespace in definitions
 * using definition node and bundle in the node
 */
class NamespaceDefinitionPlugin extends AbstractDefinitionPlugin
{
    protected $globalConfig = [];

    /**
     * NamespaceDefinitionPlugin constructor.
     *
     * Configuration:
     *
     * # Group each bundle into a separate schema definition
     * bundles:
     *      enabled:              true
     *
     *      # The following suffix will be used for bundle query groups
     *      query_suffix:         BundleQuery
     *
     *      # The following suffix will be used for bundle mutation groups
     *      mutation_suffix:      BundleMutation
     *
     *      # The following suffix will be used for bundle subscriptions groups
     *      subscription_suffix:      BundleSubscription
     *
     *      # The following bundles will be ignore for grouping, all definitions will be placed in the root query or mutation
     *      ignore:
     *
     *      # Default:
     *      - AppBundle
     *
     *      # Define aliases for bundles to set definitions inside other desired bundle name.
     *      # Can be used to group multiple bundles or publish a bundle with a different name
     *      aliases:              # Example: SecurityBundle: AppBundle
     *
     *          # Prototype
     *          name:                 ~
     *
     * # Group queries and mutations of the same node into a node specific schema definition.
     * nodes:
     *      enabled:              true
     *
     *      # The following suffix will be used to create the name for queries to the same node
     *      query_suffix:         Query
     *
     *      # The following suffix will be used to create the name for mutations to the same node
     *      mutation_suffix:      Mutation
     *
     *      # The following suffix will be used to create the name for subscriptions to the same node
     *      subscription_suffix:      Subscription
     *
     *      # The following nodes will be ignore for grouping, all definitions will be placed in the root query or mutation
     *      ignore:
     *
     *      # Default:
     *      - Node
     *
     *      # Define aliases for nodes to set definitions inside other desired node name.
     *      # Can be used to group multiple nodes or publish a node with a different group name
     *      aliases:              # Example: InvoiceItem: Invoice
     *
     *          # Prototype
     *          name:
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->globalConfig = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root): void
    {
        $config = $root
            ->info('Enable/Disable namespace for queries, subscriptions & mutations')
            ->canBeDisabled()
            ->children();

        $config->scalarNode('namespace');
        $config->scalarNode('alias');
        $config->scalarNode('node');
        $config->scalarNode('bundle');
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config): void
    {
        $node = null;
        $nodeClass = null;

        if (!($config['enabled'] ?? true)) {
            return;
        }

        if ($definition instanceof NodeAwareDefinitionInterface && isset($this->globalConfig['nodes']['enabled']) && $definition->getNode()) {
            $node = $definition->getNode();

            if (class_exists($node)) {
                $nodeClass = $node;
            } else {
                $nodeClass = $endpoint->getClassForType($node);
            }

            if (isset($this->globalConfig['nodes']['aliases'][$node])) {
                $node = $this->globalConfig['nodes']['aliases'][$node];
            }

            if ($node && \in_array($node, $this->globalConfig['nodes']['ignore'] ?? [], true)) {
                $node = null;
            }
        }

        $bundle = null;
        if ($this->globalConfig['bundles']['enabled'] ?? false) {
            if ($node && $nodeClass && $endpoint->hasType($node)) {
                preg_match_all('/\\\\?(\w+Bundle)\\\\/', $nodeClass, $matches);
                if ($matches) {
                    $bundle = current(array_reverse($matches[1]));
                }

                if (isset($this->globalConfig['bundles']['aliases'][$bundle])) {
                    $bundle = $this->globalConfig['bundles']['aliases'][$bundle];
                }

                if ($bundle && \in_array($bundle, $this->globalConfig['bundles']['ignore'] ?? [], true)) {
                    $bundle = null;
                }

                if ($bundle) {
                    $bundle = preg_replace('/Bundle$/', null, $bundle);
                }
            }
        }

        $node = $config['node'] ?? $node;
        $bundle = $config['bundle'] ?? $bundle;

        if ($bundle || $node) {
            $config = $definition->getMeta('namespace', []);
            $definition->setMeta(
                'namespace',
                array_merge(
                    [
                        'bundle' => $bundle,
                        'node' => $node,
                    ],
                    $config
                )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureEndpoint(Endpoint $endpoint): void
    {
        $groupByBundle = $this->globalConfig['bundles']['enabled'] ?? false;
        $groupByNode = $this->globalConfig['bundles']['enabled'] ?? false;
        if ($groupByBundle || $groupByNode) {
            $endpoint->setQueries($this->namespaceDefinitions($endpoint->allQueries(), $endpoint));
            $endpoint->setMutations($this->namespaceDefinitions($endpoint->allMutations(), $endpoint));
            $endpoint->setSubscriptions($this->namespaceDefinitions($endpoint->allSubscriptions(), $endpoint));
        }
    }

    /**
     * @param array    $definitions
     * @param Endpoint $endpoint
     *
     * @return array
     */
    private function namespaceDefinitions(array $definitions, Endpoint $endpoint): array
    {
        $namespacedDefinitions = [];
        /** @var DefinitionInterface $definition */
        foreach ($definitions as $definition) {
            if (!$definition->hasMeta('namespace') || !$definition->getMeta('namespace')) {
                $namespacedDefinitions[$definition->getName()] = $definition;
                continue;
            }

            $root = null;
            $parent = null;
            $namespaceConfig = $definition->getMeta('namespace');
            $namespacePath = $namespaceConfig['namespace'] ?? null;
            if ($namespacePath) {
                $querySuffix = $this->globalConfig['nodes']['query_suffix'] ?? 'Query';
                $mutationSuffix = $this->globalConfig['nodes']['mutation_suffix'] ?? 'Mutation';
                $subscriptionSuffix = $this->globalConfig['nodes']['subscription_suffix'] ?? 'Subscription';
                $namespaces = explode('/', $namespacePath);
                foreach ($namespaces as $namespace) {
                    $name = lcfirst($namespace);
                    $suffix = $querySuffix;
                    if ($definition instanceof MutationDefinition) {
                        $suffix = $mutationSuffix;
                    }
                    if ($definition instanceof SubscriptionDefinition) {
                        $suffix = $subscriptionSuffix;
                    }
                    $typeName = ucfirst($name).$suffix;
                    if (!$root) {
                        if (isset($namespacedDefinitions[$name])) {
                            $root = $namespacedDefinitions[$name];
                        } else {
                            $root = $this->createRootNamespace($definition, $name, $typeName, $endpoint);
                        }
                        $parent = $endpoint->getType($root->getType());
                        $namespacedDefinitions[$root->getName()] = $root;
                    } else {
                        $parent = $this->createChildNamespace($parent, $name, $typeName, $endpoint);
                    }
                }
                if ($alias = $namespaceConfig['alias'] ?? null) {
                    $definition->setName($alias);
                }
                $this->addDefinitionToNamespace($parent, $definition, $definition->getName());
                continue;
            }

            if ($bundle = $namespaceConfig['bundle'] ?? null) {
                $bundleQuerySuffix = $this->globalConfig['bundle']['query_suffix'] ?? 'BundleQuery';
                $bundleMutationSuffix = $this->globalConfig['bundle']['mutation_suffix'] ?? 'BundleMutation';
                $bundleSubscriptionSuffix = $this->globalConfig['bundle']['subscription_suffix'] ?? 'BundleSubscription';
                $name = lcfirst($bundle);
                $suffix = $bundleQuerySuffix;
                if ($definition instanceof MutationDefinition) {
                    $suffix = $bundleMutationSuffix;
                }
                if ($definition instanceof SubscriptionDefinition) {
                    $suffix = $bundleSubscriptionSuffix;
                }
                $typeName = ucfirst($name).$suffix;

                if (isset($namespacedDefinitions[$name])) {
                    $root = $namespacedDefinitions[$name];
                } else {
                    $root = $this->createRootNamespace($definition, $name, $typeName, $endpoint);
                }
                $parent = $endpoint->getType($root->getType());
            }

            if ($nodeName = $namespaceConfig['node'] ?? null) {
                if ($endpoint->hasTypeForClass($nodeName)) {
                    $nodeName = $endpoint->getTypeForClass($nodeName);
                }

                $name = Inflector::pluralize(lcfirst($nodeName));

                $querySuffix = $this->globalConfig['nodes']['query_suffix'] ?? 'Query';
                $mutationSuffix = $this->globalConfig['nodes']['mutation_suffix'] ?? 'Mutation';
                $subscriptionSuffix = $this->globalConfig['nodes']['subscription_suffix'] ?? 'Subscription';

                $suffix = $querySuffix;
                if ($definition instanceof MutationDefinition) {
                    $suffix = $mutationSuffix;
                }
                if ($definition instanceof SubscriptionDefinition) {
                    $suffix = $subscriptionSuffix;
                }

                $typeName = ucfirst($nodeName).$suffix;
                if (!$root) {
                    if (isset($namespacedDefinitions[$name])) {
                        $root = $namespacedDefinitions[$name];
                    } else {
                        $root = $this->createRootNamespace($definition, $name, $typeName, $endpoint);
                    }
                    $parent = $endpoint->getType($root->getType());
                } elseif ($parent) {
                    $parent = $this->createChildNamespace($parent, $name, $typeName, $endpoint);
                }

                if ($alias = $namespaceConfig['alias'] ?? null) {
                    $originName = $definition->getName();
                    $definition->setName($alias);
                } else {
                    //remove node suffix on namespaced definitions
                    $originName = $definition->getName();
                    $definition->setName(preg_replace(sprintf("/(\w+)%s$/", $nodeName), '$1', $definition->getName()));
                    $definition->setName(preg_replace(sprintf("/(\w+)%s$/", Inflector::pluralize($nodeName)), '$1', $definition->getName()));
                }
            }

            if ($root && $parent) {
                $this->addDefinitionToNamespace($parent, $definition, $originName ?? null);
                $namespacedDefinitions[$root->getName()] = $root;
            } else {
                $namespacedDefinitions[$definition->getName()] = $definition;
            }
        }

        return $namespacedDefinitions;
    }

    /**
     * @param FieldsAwareDefinitionInterface $fieldsAwareDefinition
     * @param ExecutableDefinitionInterface  $definition
     * @param string                         $originName
     */
    private function addDefinitionToNamespace(FieldsAwareDefinitionInterface $fieldsAwareDefinition, ExecutableDefinitionInterface $definition, $originName): void
    {
        $field = new FieldDefinition();
        $field->setName($definition->getName());
        $field->setOriginName($originName);
        $field->setDescription($definition->getDescription());
        $field->setDeprecationReason($definition->getDeprecationReason());
        $field->setType($definition->getType());
        $field->setResolver($definition->getResolver());
        $field->setArguments($definition->getArguments());
        $field->setList($definition->isList());
        $field->setMetas($definition->getMetas());
        $field->setNode($definition->getNode());
        $field->setComplexity($definition->getComplexity());
        $fieldsAwareDefinition->addField($field);
    }

    /**
     * @param ObjectDefinitionInterface $parent   parent definition to add a child field
     * @param string                    $name     name of the field
     * @param string                    $typeName name of the type to create
     * @param Endpoint                  $endpoint Endpoint instance to extract definitions
     *
     * @return ObjectDefinition
     */
    private function createChildNamespace(ObjectDefinitionInterface $parent, string $name, string $typeName, Endpoint $endpoint): ObjectDefinition
    {
        $child = new FieldDefinition();
        $child->setName($name);
        $child->setResolver(EmptyObjectResolver::class);

        $type = new ObjectDefinition();
        $type->setName($typeName);
        if ($endpoint->hasType($type->getName())) {
            $type = $endpoint->getType($type->getName());
        } else {
            $endpoint->add($type);
        }

        $child->setType($type->getName());
        $parent->addField($child);

        return $type;
    }

    /**
     * @param DefinitionInterface $definition Definition to create the root
     * @param string              $name       name of the root field
     * @param string              $typeName   name for the root type
     * @param Endpoint            $endpoint   Endpoint interface to extract existent definitions
     *
     * @return ExecutableDefinitionInterface
     */
    private function createRootNamespace($definition, $name, $typeName, Endpoint $endpoint): ExecutableDefinitionInterface
    {
        $class = get_class($definition);

        /** @var ExecutableDefinitionInterface $rootDefinition */
        $rootDefinition = new $class();
        $rootDefinition->setName($name);
        $rootDefinition->setResolver(EmptyObjectResolver::class);

        $type = new ObjectDefinition();
        $type->setName($typeName);
        if ($endpoint->hasType($type->getName())) {
            $type = $endpoint->getType($type->getName());
        } else {
            $endpoint->add($type);
        }

        $rootDefinition->setType($type->getName());

        return $rootDefinition;
    }
}
