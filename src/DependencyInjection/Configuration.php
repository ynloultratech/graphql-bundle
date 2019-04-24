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
use Ynlo\GraphQLBundle\Encoder\SecureIDEncoder;
use Ynlo\GraphQLBundle\Error\DefaultErrorFormatter;
use Ynlo\GraphQLBundle\Error\DefaultErrorHandler;
use Ynlo\GraphQLBundle\Subscription\PubSub\RedisPubSubHandler;
use Ynlo\GraphQLBundle\Subscription\Subscriber;

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
        $treeBuilder = new TreeBuilder('graphql');
        /** @var NodeBuilder $rootNode */
        $rootNode = $treeBuilder->root('graphql')->addDefaultsIfNotSet()->children();
        $this->configureEndpoints($rootNode);
        $this->configureSubscriptions($rootNode);
        $this->configureErrorHandling($rootNode);
        $this->configureCORS($rootNode);
        $this->configureGraphiQL($rootNode);
        $this->configurePlugins($rootNode);
        $this->configureSecurity($rootNode);
        $this->configureOthers($rootNode);
        $this->configureBCCompatibility($rootNode);

        return $treeBuilder;
    }

    protected function configureSubscriptions(NodeBuilder $root)
    {
        $subscriptions = $root->arrayNode('subscriptions')
                              ->info('Manage subscriptions settings')
                              ->addDefaultsIfNotSet()
                              ->children();

        $subscriptions->scalarNode('ttl')->defaultValue(Subscriber::DEFAULT_SUBSCRIPTION_TTL)
                      ->info('Time to live for subscriptions. The subscription will be deleted after this time, a heartbeat is required to keep-alive');
        $subscriptions->scalarNode('mercure_hub')->defaultValue('default');
        $subscriptions->scalarNode('pubsub_handler')->defaultValue(RedisPubSubHandler::class);
        $redis = $subscriptions->arrayNode('redis')->info('Configure redis server to use as subscription handler')
                               ->addDefaultsIfNotSet()
                               ->children();

        $redis->scalarNode('host')->defaultValue('localhost');
        $redis->integerNode('port')->defaultValue(6379);
        $redis->scalarNode('prefix')->defaultValue('GraphQLSubscription:')
              ->info('Define custom prefix to avoid collisions between applications');
    }

    protected function configureErrorHandling(NodeBuilder $root)
    {
        $errorHandling = $root->arrayNode('error_handling')
                              ->info('It is important to handle errors and when possible, report these errors back to your users for information. ')
                              ->addDefaultsIfNotSet()
                              ->children();

        $errorHandling->enumNode('validation_messages')
                      ->values(['error', 'payload', 'both'])
                      ->info('Where should be displayed validation messages.')
                      ->defaultValue('error');

        //@deprecated since v1.1 snd should be deleted in 2.0, this is a compatibility flag
        $errorHandling->booleanNode('jwt_auth_failure_compatibility')
                      ->info('Keep BC with oldest version of JWT Authentication errors')
                      ->setDeprecated('Since v1.1 and will will be removed in the next mayor release')
                      ->defaultFalse();

        $errorHandling->booleanNode('show_trace')->info('Show error trace in debug mode')->defaultFalse();

        $errorHandling->scalarNode('formatter')
                      ->info('Formatter is responsible for converting instances of Error to an array')
                      ->defaultValue(DefaultErrorFormatter::class);

        $errorHandling->scalarNode('handler')
                      ->info('Handler is useful for error filtering and logging.')
                      ->defaultValue(DefaultErrorHandler::class);

        $controlledErrors = $errorHandling
            ->arrayNode('controlled_errors')
            ->info('List of controlled errors')
            ->addDefaultsIfNotSet()
            ->children();

        $map = $controlledErrors->arrayNode('map')->useAttributeAsKey('code')->arrayPrototype()->children();
        $map->scalarNode('message')->isRequired();
        $map->scalarNode('description')->isRequired();
        $map->scalarNode('category')->defaultValue('user');

        $autoload = $controlledErrors
            ->arrayNode('autoload')
            ->info('Autoload exceptions implementing ControlledErrorInterface')
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->children();

        $autoload
            ->variableNode('locations')
            ->defaultValue(['Exception', 'Error'])
            ->info('Default folder to find exceptions and errors implementing controlled interface.')
            ->beforeNormalization()
            ->ifString()
            ->then(
                function ($v) {
                    return [$v];
                }
            )
            ->end();

        $autoload
            ->variableNode('whitelist')
            ->info('White listed classes')
            ->defaultValue(['/App\\\\[Exception|Error]/', '/\w+Bundle\\\\[Exception|Error]/'])
            ->beforeNormalization()
            ->ifString()
            ->then(
                function ($v) {
                    return [$v];
                }
            )
            ->end()
            ->validate()
            ->ifTrue(
                function (array $value) {
                    foreach ($value as $val) {
                        try {
                            preg_match($val, null);
                        } catch (\Exception $exception) {
                            return true;
                        }
                    }
                }
            )->thenInvalid('Invalid regular expression');

        $autoload
            ->variableNode('blacklist')
            ->info('Black listed classes')
            ->beforeNormalization()
            ->ifString()
            ->then(
                function ($v) {
                    return [$v];
                }
            )
            ->end()
            ->validate()
            ->ifTrue(
                function (array $value) {
                    foreach ($value as $val) {
                        try {
                            preg_match($val, null);
                        } catch (\Exception $exception) {
                            return true;
                        }
                    }
                }
            )->thenInvalid('Invalid regular expression');
    }

    protected function configureEndpoints(NodeBuilder $root)
    {
        $endpoints = $root->arrayNode('endpoints')
                          ->useAttributeAsKey('name')
                          ->validate()
                          ->ifTrue(
                              function ($v) {
                                  return array_key_exists('default', $v);
                              }
                          )->thenInvalid('"default" can\'t be used as endpoint name, the system internally use this endpoint name to store the entire schema.')
                          ->end()
                          ->arrayPrototype()
                          ->children();

        $endpoints->arrayNode('roles')
                  ->beforeNormalization()
                  ->ifString()
                  ->then(
                      function ($v) {
                          return preg_split('/\s*,\s*/', $v);
                      }
                  )
                  ->end()
                  ->prototype('scalar')
                  ->end();

        $endpoints->scalarNode('host')->example('^api\.backend\.');
        $endpoints->scalarNode('path')->example('/backend');

        $root->arrayNode('endpoint_alias')
             ->info('Use alias to refer to multiple endpoints using only one name')
             ->useAttributeAsKey('name')
             ->beforeNormalization()
             ->ifString()
             ->then(
                 function ($v) {
                     return preg_split('/\s*,\s*/', $v);
                 }
             )
             ->end()
             ->variablePrototype();

        $root->scalarNode('endpoint_default')->info('Endpoint to apply to all definitions without explicit endpoint.');

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

        $graphiql->scalarNode('template')->defaultValue('@YnloGraphQL/explorer.html.twig');
        $graphiql->scalarNode('default_query')->defaultNull()->info('An optional GraphQL string to use when no query exists from a previous session. If none is provided, GraphiQL will use its own default query.');

        $graphiql->scalarNode('favicon')->info('Url or path to favicon');

        $docs = $graphiql->arrayNode('documentation')->info('Display external API documentation link')->addDefaultsIfNotSet()->children();
        $docs->scalarNode('link')->info('Url, route or path.');
        $docs->scalarNode('btn_label')->defaultValue('Documentation');
        $docs->scalarNode('btn_class')->defaultValue('btn btn-outline-success');

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

        //the updated version of `jwt` to use lexik authentication bundle
        $lexikJwt = $authenticationProvider->arrayNode('lexik_jwt')
                                           ->canBeEnabled()
                                           ->children();

        $lexikJwt->scalarNode('user_provider')
                 ->isRequired()
                 ->info('Name of the user provider to use');

        $lexikJwt->scalarNode('username_label')
                 ->defaultValue('Username');

        $lexikJwt->scalarNode('password_label')
                 ->defaultValue('Password');

        $authenticationProvider->scalarNode('custom')
                               ->defaultNull()
                               ->info('Configure custom service to use as authentication provider');

        //deprecated since v1.1 and should be deleted in v2.0
        $jwt = $authenticationProvider->arrayNode('jwt')
                                      ->setDeprecated('Use lexik_jwt instead, this provider will be removed in the next mayor release.')
                                      ->canBeEnabled()
                                      ->children();

        $jwtLogin = $jwt->arrayNode('login')->children();

        $jwtLogin->scalarNode('url')
                 ->info('Route name or URI to make the login process to retrieve the token.')
                 ->isRequired();

        $jwtLogin->scalarNode('username_parameter')
                 ->defaultValue('username');

        $jwtLogin->scalarNode('username_label')
                 ->defaultValue('Username');

        $jwtLogin->scalarNode('password_parameter')
                 ->defaultValue('password');

        $jwtLogin->scalarNode('password_label')
                 ->defaultValue('Password');

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

    protected function configurePlugins(NodeBuilder $root)
    {
        $this->configurePluginPaginationGlobalConfig($root);
        $this->configurePluginNamespaceGlobalConfig($root);
    }

    protected function configurePluginPaginationGlobalConfig(NodeBuilder $root)
    {
        $pagination = $root->arrayNode('pagination')->addDefaultsIfNotSet()->children();
        $pagination->integerNode('limit')
                   ->defaultValue(100)->info('Maximum limit allowed for all paginations');
    }

    protected function configurePluginNamespaceGlobalConfig(NodeBuilder $root)
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

        $bundles->scalarNode('subscription_suffix')
                ->info('The following suffix will be used for bundle subscription groups')
                ->defaultValue('BundleSubscription');

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

        $nodes->scalarNode('subscription_suffix')
              ->info('The following suffix will be used to create the name for subscriptions to the same node')
              ->defaultValue('Subscriptions');

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

    private function configureOthers(NodeBuilder $rootNode)
    {
        $rootNode
            ->scalarNode('id_encoder')
            ->defaultValue(SecureIDEncoder::class)
            ->info('Service used to encode nodes identifiers, must implements IDEncoderInterface');
    }

    private function configureBCCompatibility(NodeBuilder $rootNode)
    {
        $bcNode = $rootNode
            ->arrayNode('bc')
            ->info('Backward compatibility layer to keep deprecated features during some time to upgrade API consumers progressively.')
            ->addDefaultsIfNotSet()
            ->children();

        $bcNode->variableNode('filters')
               ->info('Keep deprecated "filters" argument in collections')
               ->setDeprecated('v1.2')
               ->defaultFalse();

        $bcNode->variableNode('orderBy')
               ->info('Keep deprecated "orderBy" argument in collections')
               ->setDeprecated('v1.2')
               ->defaultFalse();
    }
}
