<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        /** @var NodeBuilder $rootNode */
        $rootNode = $treeBuilder->root('graphql')->addDefaultsIfNotSet()->children();
        $this->configureDefinition($rootNode);

        return $treeBuilder;
    }

    protected function configureDefinition(NodeBuilder $root)
    {
        $definitions = $root->arrayNode('definitions')->addDefaultsIfNotSet()->children();

        $extensions = $definitions->arrayNode('extensions')->addDefaultsIfNotSet();
        $this->configureExtensionPagination($extensions->children());
        $this->configureExtensionNamespace($extensions->children());
    }

    protected function configureExtensionPagination(NodeBuilder $root)
    {
        $pagination = $root->arrayNode('pagination')->addDefaultsIfNotSet()->children();
        $pagination->integerNode('limit')
                   ->defaultValue(100)->info('Maximum limit allowed for all paginations');
    }

    protected function configureExtensionNamespace(NodeBuilder $root)
    {
        $namespaces = $root->arrayNode('namespaces')
                           ->info(
                               'Group GraphQL schema using namespaced schemas. 
On large schemas is  helpful to keep schemas grouped by bundle and node'
                           )
                           ->canBeEnabled()
                           ->addDefaultsIfNotSet()
                           ->children();

        $bundles = $namespaces->arrayNode('bundles')
                              ->info('Group each bundle into a separate schema definition')
                              ->canBeDisabled()
                              ->addDefaultsIfNotSet()
                              ->children();

        $bundles->scalarNode('suffix')
                ->info('The following suffix will be used for bundle groups')
                ->defaultValue('Bundle');

        $bundles->variableNode('ignore')
                ->info('The following bundles will be ignore for grouping, all definitions will be placed in the root query or mutation')
                ->defaultValue(['AppBundle']);

        $bundles->arrayNode('aliases')
                ->info(
                    'Define aliases for bundles to set definitions inside other desired bundle name. 
Can be used to group multiple bundles or publish a bundle with a different name'
                )
                ->example('SecurityBundle: AppBundle')
                ->useAttributeAsKey('name')
                ->prototype('scalar');


        $nodes = $namespaces->arrayNode('nodes')
                            ->info('Group queries and mutations of the same node into a node specific schema definition.')
                            ->addDefaultsIfNotSet()
                            ->canBeDisabled()
                            ->children();

        $nodes->scalarNode('query_suffix')
              ->info('The following suffix will be used to create the name for queries to the same node')
              ->defaultValue('Query');

        $nodes->scalarNode('mutation_suffix')
              ->info('The following suffix will be used to create the name for mutations to the same node')
              ->defaultValue('Mutation');

        $nodes->variableNode('ignore')
              ->info('The following nodes will be ignore for grouping, all definitions will be placed in the root query or mutation')
              ->defaultValue(['Node']);

        $nodes->arrayNode('aliases')
              ->info(
                  'Define aliases for nodes to set definitions inside other desired node name. 
Can be used to group multiple nodes or publish a node with a different group name'
              )
              ->example('InvoiceItem: Invoice')
              ->useAttributeAsKey('name')
              ->prototype('scalar');
    }
}
