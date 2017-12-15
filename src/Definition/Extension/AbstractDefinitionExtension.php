<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Extension;

use Doctrine\Common\Util\Inflector;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * AbstractDefinitionExtension
 */
abstract class AbstractDefinitionExtension implements DefinitionExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        $name = get_class($this);
        preg_match('/(\w+)$/', $name, $matches);
        $name = preg_replace('/Extension$/', null, $matches[1]);
        $name = preg_replace('/Definition$/', null, $name);

        return Inflector::tableize($name);
    }

    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function normalizeConfig(DefinitionInterface $definition, $config): array
    {
        if (is_bool($config)) {
            $config = ['enabled' => $config];
        }

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config)
    {
        // TODO: Implement configure() method.
    }

    /**
     * {@inheritDoc}
     */
    public function configureEndpoint(Endpoint $endpoint)
    {
        // TODO: Implement configureEndpoint() method.
    }
}