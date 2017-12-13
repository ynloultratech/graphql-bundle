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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Definitions extensions power up definitions
 * adding extra schema to resolved and parsed definitions
 */
interface DefinitionExtensionInterface
{
    /**
     * Unique name of the extension
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Build the extension configuration
     * the resolved & normalized configuration will be based to configureDefinition
     *
     * @see graphql:definition:options to expose all extensions configurations
     *
     * @param ArrayNodeDefinition $root
     */
    public function buildConfig(ArrayNodeDefinition $root);

    /**
     * Use this method to normalize/override the configuration just before resolve.
     * Can use this to allow the use of shortcuts or to resolve default config based on definition
     *
     * e.g. A configuration like [pagination:true] can be normalized as [pagination:[target: Node]]
     *
     * @param DefinitionInterface $definition
     * @param mixed               $config
     *
     * @return array normalized definition configuration, should be compatible with defined configuration in :buildConfig()
     */
    public function normalizeConfig(DefinitionInterface $definition, $config): array;

    /**
     * Use this to override or customize definitions
     *
     * @param DefinitionInterface $definition definition to configure
     * @param Endpoint            $endpoint   endpoint with definitions
     * @param array               $config     resolved config to use
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config);

    /**
     * After configure all definitions can need configure the endpoints to do some general tasks,
     * like normalize types, check integrity or order schema
     *
     * @param Endpoint $endpoint
     */
    public function configureEndpoint(Endpoint $endpoint);
}
