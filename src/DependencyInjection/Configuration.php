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

use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
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
        $this->configureCORS($rootNode);
        $this->configureGraphiQL($rootNode);
        $this->configureDefinition($rootNode);
        $this->configureSecurity($rootNode);

        return $treeBuilder;
    }

    protected function configureGraphiQL(NodeBuilder $root)
    {
        $graphiql = $root->arrayNode('graphiql')->addDefaultsIfNotSet()->children();

        $graphiql->scalarNode('title')
                 ->defaultValue('GraphQL API Explorer');

        $graphiql
            ->scalarNode('data_warning_message')
            ->defaultValue('Heads up! GraphQL Explorer makes use of your <strong>real</strong>, <strong>live</strong>, <strong>production</strong> data.');
        $graphiql->booleanNode('data_warning_dismissible')->defaultTrue();
        $graphiql->enumNode('data_warning_style')->values(['info', 'warning', 'danger'])->defaultValue('danger');

        $graphiql->scalarNode('template')
                 ->defaultValue('@YnloGraphQL/explorer.html.twig');

        $authentication = $graphiql->arrayNode('authentication')->addDefaultsIfNotSet()->children();
        $authentication
            ->booleanNode('required')
            ->info(
                'The API require credentials to make any requests, 
if this value is FALSE and a provider is specified the authentication is optional.'
            )
            ->defaultFalse();

        $authentication->scalarNode('login_message')
                       ->defaultValue('Start exploring GraphQL API queries using your account’s data now.');

        $authenticationProvider = $authentication->arrayNode('provider')->children();

        $jwt = $authenticationProvider->arrayNode('jwt')->canBeEnabled()->children();

        $jwtLogin = $jwt->arrayNode('login')->children();

        $jwtLogin->scalarNode('url')
                 ->info('Route name or URI to make the login process to retrieve the token.')
                 ->isRequired();

        $jwtLogin->scalarNode('username_parameter')
                 ->defaultValue('username');

        $jwtLogin->scalarNode('password_parameter')
                 ->defaultValue('password');

        $jwtLogin->enumNode('parameters_in')
                 ->values(['form', 'query', 'header'])
                 ->info('How pass parameters to request the token')
                 ->defaultValue('form');

        $jwtLogin->scalarNode('response_token_path')
                 ->defaultValue('token')
                 ->info('Where the token should be located in the response in case of JSON, set null if the response is the token.');

        $jwtRequests = $jwt->arrayNode('requests')->addDefaultsIfNotSet()->children();

        $jwtRequests->enumNode('token_in')
                    ->values(['query', 'header'])
                    ->info('Where should be located the token on every request')
                    ->defaultValue('header');

        $jwtRequests->scalarNode('token_name')
                    ->defaultValue('Authorization')
                    ->info('Name of the token in query or header name');

        $jwtRequests->scalarNode('token_template')
                    ->defaultValue('Bearer {token}')
                    ->info('Customize how the token should be send,  use the place holder {token} to replace for current token');

        $authenticationProvider->scalarNode('custom')
                               ->defaultNull()
                               ->info('Configure custom service to use as authentication provider');
    }

    protected function configureCORS(NodeBuilder $root)
    {
        $cors = $root->arrayNode('cors')->canBeEnabled()->children();
        $cors->booleanNode('allow_credentials')->defaultTrue();
        $cors->variableNode('allow_headers')->defaultValue(['Origin', 'Content-Type', 'Accept', 'Authorization']);
        $cors->integerNode('max_age')->defaultValue(3600);
        $cors->variableNode('allow_methods')->defaultValue(['POST', 'GET', 'OPTIONS']);
        $cors->variableNode('allow_origins')->defaultValue(['*']);
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

        $bundles->scalarNode('query_suffix')
                ->info('The following suffix will be used for bundle query groups')
                ->defaultValue('BundleQuery');

        $bundles->scalarNode('mutation_suffix')
                ->info('The following suffix will be used for bundle mutation groups')
                ->defaultValue('BundleMutation');

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

    private function configureSecurity(NodeBuilder $rootNode)
    {
        $securityNode = $rootNode
            ->arrayNode('security')
            ->canBeEnabled()
            ->children();

        $validationRulesNode = $securityNode
            ->arrayNode('validation_rules')
            ->addDefaultsIfNotSet()
            ->children();
        $validationRulesNode
            ->integerNode('query_complexity')
            ->info('Query complexity score before execution. (Recommended >= 200)')
            ->min(0)
            ->defaultValue(QueryComplexity::DISABLED);
        $validationRulesNode
            ->integerNode('query_depth')
            ->info('Max depth of the query. (Recommended >= 11)')
            ->min(0)
            ->defaultValue(QueryDepth::DISABLED);
        $validationRulesNode
            ->booleanNode('disable_introspection')
            ->defaultFalse();
    }
}
