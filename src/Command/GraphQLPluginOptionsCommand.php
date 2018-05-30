<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ynlo\GraphQLBundle\Definition\Plugin\DefinitionPluginInterface;
use Ynlo\GraphQLBundle\Definition\Plugin\GraphQLDefinitionPluginManager;

/**
 * GraphQLPluginOptionsCommand
 */
class GraphQLPluginOptionsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('graphql:plugins')
             ->setDescription('Expose all available options for one or all graphql plugins')
             ->addArgument('plugin', InputArgument::OPTIONAL, 'Show only options for given plugin');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filterBy = null;
        if ($input->hasArgument('plugin')) {
            $filterBy = $input->getArgument('plugin');
        }

        $extensions = $this->getContainer()->get(GraphQLDefinitionPluginManager::class)->getExtensions();

        $dumped = false;
        foreach ($extensions as $extension) {
            if ($filterBy && $extension->getName() !== $filterBy) {
                continue;
            }
            $config = $this->createConfig($extension);
            $dumper = new YamlReferenceDumper();
            $dump = $dumper->dump($config);
            if (substr_count($dump, "\n") > 1) {
                $output->writeln($dump);
                $dumped = true;
            }
        }

        if ($filterBy && !$dumped) {
            throw new \InvalidArgumentException('The plugin does not exist or not have configuration');
        }
    }

    /**
     * @param DefinitionPluginInterface $extension
     *
     * @return ConfigurationInterface
     */
    protected function createConfig(DefinitionPluginInterface $extension): ConfigurationInterface
    {
        return new class($extension) implements ConfigurationInterface
        {
            /**
             * @var DefinitionPluginInterface
             */
            protected $extension;

            /**
             *  constructor.
             *
             * @param DefinitionPluginInterface $extension
             */
            public function __construct(DefinitionPluginInterface $extension)
            {
                $this->extension = $extension;
            }

            /**
             * @return TreeBuilder
             */
            public function getConfigTreeBuilder()
            {
                $treeBuilder = new TreeBuilder();
                $root = $treeBuilder->root($this->extension->getName());
                $this->extension->buildConfig($root);

                return $treeBuilder;
            }
        };
    }
}
