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
            foreach ($config['definitions']['extensions'] as $extension => $config) {
                $config = isset($config['enabled']) && $config['enabled'] ? $config : [];
                $container->setParameter('graphql.extension_config.'.$extension, $config ?? []);
            }
        }

        $configDir = __DIR__.'/../Resources/config';
        $loader = new YamlFileLoader($container, new FileLocator($configDir));
        $loader->load('services.yml');
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'graphql';
    }
}
