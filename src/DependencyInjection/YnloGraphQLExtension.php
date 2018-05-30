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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Ynlo\GraphQLBundle\Cache\DefinitionCacheWarmer;
use Ynlo\GraphQLBundle\GraphiQL\JWTGraphiQLAuthentication;

/**
 * Class YnloGraphQLExtension
 */
class YnloGraphQLExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['definitions']['plugins']['namespaces']['bundles']['aliases']['GraphQLBundle'])) {
            $config['definitions']['plugins']['namespaces']['bundles']['aliases']['GraphQLBundle'] = 'AppBundle';
        }

        $container->setParameter('graphql.config', $config);
        if (isset($config['definitions']['plugins'])) {
            foreach ($config['definitions']['plugins'] as $plugin => $pluginConfig) {
                $pluginConfig = isset($pluginConfig['enabled']) && $pluginConfig['enabled'] ? $pluginConfig : [];
                $container->setParameter('graphql.plugin_config.'.$plugin, $pluginConfig ?? []);
            }
        }

        $container->setParameter('graphql.cors_config', $config['cors'] ?? []);
        $container->setParameter('graphql.graphiql', $config['graphiql'] ?? []);
        $container->setParameter('graphql.graphiql_auth_jwt', $config['graphiql']['authentication']['provider']['jwt'] ?? []);
        $container->setParameter('graphql.security.validation_rules', $config['security']['validation_rules'] ?? []);

        $graphiQLAuthProvider = null;
        if ($config['graphiql']['authentication']['provider']['jwt']['enabled'] ?? false) {
            $graphiQLAuthProvider = JWTGraphiQLAuthentication::class;
        }
        if ($config['graphiql']['authentication']['provider']['custom'] ?? false) {
            $graphiQLAuthProvider = $config['graphiql']['authentication']['provider']['custom'];
        }
        $container->setParameter('graphql.graphiql_auth_provider', $graphiQLAuthProvider);

        $configDir = __DIR__.'/../Resources/config';
        $loader = new YamlFileLoader($container, new FileLocator($configDir));
        $loader->load('services.yml');

        if (!$container->getParameter('kernel.debug')) {
            $container->getDefinition(DefinitionCacheWarmer::class)->clearTag('kernel.event_subscriber');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'graphql';
    }
}
