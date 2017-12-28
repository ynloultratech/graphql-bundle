<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Extension;

use Doctrine\Common\Util\Inflector;
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
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\EmptyObjectResolver;

/**
 * This extension configure namespace in definitions
 * using definition node and bundle in the node
 */
class NamespaceDefinitionExtension extends AbstractDefinitionExtension
{
    /**
     * @var array
     */
    protected $globalConfig = [];

    /**
     * PaginationDefinitionExtension constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->globalConfig = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root)
    {
        $root
            ->info('Enable/Disable namespace for queries and mutations')
            ->canBeDisabled()
            ->children();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config)
    {
        $node = null;
        $nodeClass = null;

        if (!($config['enabled'] ?? true)) {
            return;
        }

        if (($this->globalConfig['nodes']['enabled'] ?? false) && $definition instanceof NodeAwareDefinitionInterface && $definition->getNode()) {
            $node = $definition->getNode();

            if (class_exists($node)) {
                $nodeClass = $node;
            } else {
                $nodeClass = $endpoint->getClassForType($node);
            }

            if (!is_a($nodeClass, NodeInterface::class, true)) {
                return;
            }

            if (isset($this->globalConfig['nodes']['aliases'][$node])) {
                $node = $this->globalConfig['nodes']['aliases'][$node];
            }

            if ($node && in_array($node, $this->globalConfig['nodes']['ignore'])) {
                $node = null;
            }
        }

        $bundle = null;
        if ($this->globalConfig['bundles']['enabled'] ?? false) {
            if ($node && $nodeClass) {
                if ($endpoint->hasType($node) && $nodeClass) {
                    preg_match_all('/\\\\?(\w+Bundle)\\\\/', $nodeClass, $matches);
                    if ($matches) {
                        $bundle = current(array_reverse($matches[1]));
                    }

                    if (isset($this->globalConfig['bundles']['aliases'][$bundle])) {
                        $bundle = $this->globalConfig['bundles']['aliases'][$bundle];
                    }

                    if ($bundle && in_array($bundle, $this->globalConfig['bundles']['ignore'])) {
                        $bundle = null;
                    }

                    if ($bundle) {
                        $bundle = preg_replace('/Bundle$/', null, $bundle);
                    }
                }
            }
        }

        if ($bundle || $node) {
            $definition->setMeta('namespace', ['bundle' => $bundle, 'node' => $node]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureEndpoint(Endpoint $endpoint)
    {
        $groupByBundle = $this->globalConfig['bundles']['enabled'] ?? false;
        $groupByNode = $this->globalConfig['bundles']['enabled'] ?? false;
        if ($groupByBundle || $groupByNode) {
            $endpoint->setQueries($this->namespaceDefinitions($endpoint->allQueries(), $endpoint));
            $endpoint->setMutations($this->namespaceDefinitions($endpoint->allMutations(), $endpoint));
        }
    }

    /**
     * @param array    $definitions
     * @param Endpoint $endpoint
     *
     * @return array
     */
    private function namespaceDefinitions($definitions, Endpoint $endpoint)
    {
        $namespacedDefinitions = [];
        /** @var DefinitionInterface $definition */
        foreach ($definitions as $definition) {
            if (!$definition->hasMeta('namespace') || !$definition->getMeta('namespace')) {
                $namespacedDefinitions[] = $definition;
                continue;
            }

            $root = null;
            $parent = null;
            $namespace = $definition->getMeta('namespace');
            if ($bundle = $namespace['bundle'] ?? null) {
                $bundleSuffix = $this->globalConfig['bundles']['suffix'] ?? 'Bundle';
                $name = lcfirst($bundle);
                $typeName = ucfirst($name).$bundleSuffix;
                $root = $this->createRootNamespace(get_class($definition), $name, $typeName, $endpoint);
                $parent = $endpoint->getType($root->getType());
            }

            if ($nodeName = $namespace['node'] ?? null) {
                if ($endpoint->hasTypeForClass($nodeName)) {
                    $nodeName = $endpoint->getTypeForClass($nodeName);
                }

                $name = Inflector::pluralize(lcfirst($nodeName));

                $querySuffix = $this->globalConfig['nodes']['query_suffix'] ?? 'Query';
                $mutationSuffix = $this->globalConfig['nodes']['mutation_suffix'] ?? 'Mutation';

                $typeName = ucfirst($nodeName).(($definition instanceof MutationDefinition) ? $mutationSuffix : $querySuffix);
                if (!$root) {
                    $root = $this->createRootNamespace(get_class($definition), $name, $typeName, $endpoint);
                    $parent = $endpoint->getType($root->getType());
                } elseif ($parent) {
                    $parent = $this->createChildNamespace($parent, $name, $typeName, $endpoint);
                }

                //remove node suffix on namespaced definitions
                $definition->setName(preg_replace(sprintf("/(\w+)%s$/", $nodeName), '$1', $definition->getName()));
                $definition->setName(preg_replace(sprintf("/(\w+)%s$/", Inflector::pluralize($nodeName)), '$1', $definition->getName()));

            }

            if ($root && $parent) {
                $this->addDefinitionToNamespace($parent, $definition);
                $namespacedDefinitions[] = $root;
            }
        }

        return $namespacedDefinitions;
    }

    /**
     * @param FieldsAwareDefinitionInterface $fieldsAwareDefinition
     * @param ExecutableDefinitionInterface  $definition
     */
    private function addDefinitionToNamespace(FieldsAwareDefinitionInterface $fieldsAwareDefinition, ExecutableDefinitionInterface $definition)
    {
        $field = new FieldDefinition();
        $field->setName($definition->getName());
        $field->setType($definition->getType());
        $field->setResolver($definition->getResolver());
        $field->setArguments($definition->getArguments());
        $field->setList($definition->isList());
        $field->setMetas($definition->getMetas());
        $field->setNode($definition->getNode());
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
    private function createChildNamespace(ObjectDefinitionInterface $parent, $name, $typeName, Endpoint $endpoint): ObjectDefinition
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
     * @param string   $rootType Class of the root type to create QueryDefinition or MutationDefinition
     * @param string   $name     name of the root field
     * @param string   $typeName name for the root type
     * @param Endpoint $endpoint Endpoint interface to extract existent definitions
     *
     * @return ExecutableDefinitionInterface
     */
    private function createRootNamespace($rootType, $name, $typeName, Endpoint $endpoint): ExecutableDefinitionInterface
    {
        /** @var ExecutableDefinitionInterface $rootDefinition */
        $rootDefinition = new $rootType();
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
