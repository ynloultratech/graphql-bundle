<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Plugin;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

abstract class AbstractDefinitionPlugin implements DefinitionPluginInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        $name = \get_class($this);
        preg_match('/(\w+)$/', $name, $matches);
        $name = preg_replace('/Plugin/', null, $matches[1]);
        $name = preg_replace('/Definition$/', null, $name);

        return Inflector::tableize($name);
    }

    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeConfig(DefinitionInterface $definition, $config): array
    {
        if (\is_bool($config)) {
            $config = ['enabled' => $config];
        }

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function configureEndpoint(Endpoint $endpoint): void
    {
    }
}
