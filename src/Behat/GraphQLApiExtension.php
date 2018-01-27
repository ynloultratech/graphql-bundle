<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Ynlo\GraphQLBundle\Behat\Transformer\TransformStringToExpression;

/**
 * GraphQLBundle extension for Behat.
 */
class GraphQLApiExtension implements Extension
{
    const CONTEXTS_PARAMETER = 'graphql.contexts';

    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes extension.
     *
     * @param null|ServiceProcessor $processor
     */
    public function __construct(ServiceProcessor $processor = null)
    {
        $this->processor = $processor ? : new ServiceProcessor();
    }

    public function getConfigKey()
    {
        return 'graphql';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
        if (!$extensionManager->getExtension('symfony2')) {
            throw new \RuntimeException(
                'The behat "Symfony2Extension" is required to work with "GraphQLApiExtension". 
Ensure you have "Behat\Symfony2Extension" inside your behat config file.'
            );
        }
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $root = $builder->addDefaultsIfNotSet()->children();

        $client = $root->arrayNode('client')->addDefaultsIfNotSet()->children();
        $client->booleanNode('insulated')->defaultFalse();
    }

    public function process(ContainerBuilder $container)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Resources/config'));
        $loader->load('services.yml');
        $container->setParameter('graphql.client_config', $config['client']);

        $this->processExpressionPreprocessors($container);
    }

    private function processExpressionPreprocessors(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, 'graphql.expression_preprocessor');
        $definition = $container->getDefinition(TransformStringToExpression::class);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerPreprocessor', array($reference));
        }
    }
}
