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
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['definitions']['extensions']['namespaces']['bundles']['aliases']['GraphQLBundle'])) {
            $config['definitions']['extensions']['namespaces']['bundles']['aliases']['GraphQLBundle'] = 'AppBundle';
        }

        $container->setParameter('graphql.config', $config);
        if (isset($config['definitions']['extensions'])) {
            foreach ($config['definitions']['extensions'] as $extension => $extConfig) {
                $extConfig = isset($extConfig['enabled']) && $extConfig['enabled'] ? $extConfig : [];
                $container->setParameter('graphql.extension_config.'.$extension, $extConfig ?? []);
            }
        }

        $container->setParameter('graphql.cors_config', $config['cors'] ?? []);
        $container->setParameter('graphql.graphiql', $config['graphiql'] ?? []);
        $container->setParameter('graphql.graphiql_auth_jwt', $config['graphiql']['authentication']['provider']['jwt'] ?? []);

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
