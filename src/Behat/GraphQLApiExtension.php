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
use Ynlo\GraphQLBundle\Behat\Authentication\FosUserResolver;
use Ynlo\GraphQLBundle\Behat\Authentication\JWT\LexikJWTGenerator;
use Ynlo\GraphQLBundle\Behat\Transformer\TransformStringToExpression;

/**
 * GraphQLBundle extension for Behat.
 */
class GraphQLApiExtension implements Extension
{
    const CONTEXTS_PARAMETER = 'graphql.contexts';

    private static $config = [];

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

        $root->scalarNode('route');

        $authentication = $root->arrayNode('authentication')->children();
        $jwt = $authentication->arrayNode('jwt')->canBeEnabled()->children();

        $jwt->scalarNode('generator')
            ->defaultValue(LexikJWTGenerator::class);

        $jwt->scalarNode('user_resolver')
            ->defaultValue(FosUserResolver::class);

        $jwt->arrayNode('users')
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
    }

    public function process(ContainerBuilder $container)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
        self::$config = $config;

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Resources/config'));
        $loader->load('services.yml');
        $container->setParameter('graphql.client_config', $config['client']);

        $this->processExpressionPreprocessors($container);
    }

    private function processExpressionPreprocessors(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, 'graphql.expression_preprocessor');
        $definition = $container->getDefinition(TransformStringToExpression::class);

        if (!class_exists('Doctrine\Common\DataFixtures\Loader')) {
            $container->removeDefinition('Ynlo\GraphQLBundle\Behat\Fixtures\LoadFixturesSubscriber');
        }

        foreach ($references as $reference) {
            $definition->addMethodCall('registerPreprocessor', array($reference));
        }
    }

    /**
     * @return mixed
     */
    public static function getConfig(): array
    {
        return self::$config;
    }
}
