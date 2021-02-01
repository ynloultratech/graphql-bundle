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

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Exception\ProcessingException;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Ynlo\GraphQLBundle\Behat\Authentication\JWT\LexikJWTGenerator;
use Ynlo\GraphQLBundle\Behat\Authentication\UserResolver;
use Ynlo\GraphQLBundle\Behat\Context\Initializer\KernelAwareInitializer;
use Ynlo\GraphQLBundle\Behat\Fixtures\LoadFixturesSubscriber;
use Ynlo\GraphQLBundle\Behat\Transformer\TransformStringToExpression;

/**
 * GraphQLBundle extension for Behat.
 */
class GraphQLApiExtension implements Extension
{
    const CONTEXTS_PARAMETER = 'graphql.contexts';
    const KERNEL_ID = 'graphql.symfony_kernel';
    const DEFAULT_KERNEL_BOOTSTRAP = 'app/autoload.php';

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
        $this->processor = $processor ?: new ServiceProcessor();
    }

    public function getConfigKey()
    {
        return 'graphql';
    }

    public function initialize(ExtensionManager $extensionManager)
    {

    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $root = $builder->addDefaultsIfNotSet()->children();

        $client = $root->arrayNode('client')->addDefaultsIfNotSet()->children();
        $client->booleanNode('insulated')->defaultFalse();

        $root->scalarNode('route');

        $boolFilter = function ($v) {
            $filtered = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return (null === $filtered) ? $v : $filtered;
        };

        $kernel = $root->arrayNode('kernel')->addDefaultsIfNotSet()->children();
        $kernel
            ->scalarNode('bootstrap')
            ->defaultValue('features/bootstrap/autoload.php');

        $kernel
            ->scalarNode('path')
            ->defaultValue('src/Kernel.php');

        $kernel
            ->scalarNode('class')
            ->defaultValue('App\Kernel');

        $kernel->booleanNode('debug')
               ->beforeNormalization()
               ->ifString()->then($boolFilter)
               ->end()
               ->defaultTrue();

        $kernel
            ->scalarNode('env')
            ->defaultValue('test');

        $context = $root->arrayNode('context')->addDefaultsIfNotSet()->children();

        $context
            ->scalarNode('path_suffix')
            ->defaultValue('Features');

        $context
            ->scalarNode('class_suffix')
            ->defaultValue('Features\Context\FeatureContext');

        $authentication = $root->arrayNode('authentication')->addDefaultsIfNotSet()->children();
        $jwt = $authentication->arrayNode('jwt')->addDefaultsIfNotSet()->canBeEnabled()->children();

        $jwt->scalarNode('generator')
            ->defaultValue(LexikJWTGenerator::class);

        $jwt->scalarNode('user_resolver')
            ->defaultValue(UserResolver::class);
    }

    public function process(ContainerBuilder $container)
    {
        // get base path
        $basePath = $container->getParameter('paths.base');

        // find and require bootstrap
        $bootstrapPath = $container->getParameter('graphql.symfony_kernel.bootstrap');
        if ($bootstrapPath) {
            if (file_exists($bootstrap = $basePath.'/'.$bootstrapPath)) {
                require_once($bootstrap);
            } elseif (file_exists($bootstrapPath)) {
                require_once($bootstrapPath);
            } elseif ($bootstrapPath !== self::DEFAULT_KERNEL_BOOTSTRAP) {
                throw new ProcessingException(
                    'Could not load bootstrap file. Please check your configuration at "kernel.bootstrap"'
                );
            }
        }

        // find and require kernel
        $kernelPath = $container->getParameter('graphql.symfony_kernel.path');
        if (file_exists($kernel = $basePath.'/'.$kernelPath)) {
            $container->getDefinition(self::KERNEL_ID)->setFile($kernel);
        } elseif (file_exists($kernelPath)) {
            $container->getDefinition(self::KERNEL_ID)->setFile($kernelPath);
        }
    }

    public function load(ContainerBuilder $container, array $config)
    {
        self::$config = $config;

        $this->loadContextInitializer($container);
        $this->loadKernel($container, $config['kernel']);

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
            $container->removeDefinition(LoadFixturesSubscriber::class);
        }

        foreach ($references as $reference) {
            $definition->addMethodCall('registerPreprocessor', [$reference]);
        }
    }

    private function loadContextInitializer(ContainerBuilder $container)
    {
        $definition = new Definition(
            KernelAwareInitializer::class, [
                new Reference(self::KERNEL_ID),
            ]
        );
        $definition->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG, ['priority' => 0]);
        $container->setDefinition(KernelAwareInitializer::class, $definition);
    }

    private function loadKernel(ContainerBuilder $container, array $config)
    {
        $definition = new Definition(
            $config['class'], [
                $config['env'],
                $config['debug'],
            ]
        );
        $definition->addMethodCall('boot');
        $container->setDefinition(self::KERNEL_ID, $definition);
        $container->setParameter(self::KERNEL_ID.'.path', $config['path']);
        $container->setParameter(self::KERNEL_ID.'.bootstrap', $config['bootstrap']);
    }

    /**
     * @return mixed
     */
    public static function getConfig(): array
    {
        return self::$config;
    }
}
