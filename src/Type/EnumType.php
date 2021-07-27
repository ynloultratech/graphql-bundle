<?php
/*
 * ******************************************************************************
 * This file is part of the GraphQL Bundle package.
 *
 * (c) YnloUltratech <support@ynloultratech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  *****************************************************************************
 */

namespace Ynlo\GraphQLBundle\Type;

use GraphQL\Type\Definition\EnumType as GraphQLEnumType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Type\Definition\EndpointAwareInterface;
use Ynlo\GraphQLBundle\Type\Definition\EndpointAwareTrait;

/**
 * This enum type add support to use endpoints config for each value in order to hide/show some values based on endpoint
 * extends from this EnumType and use a config like:
 *  $config = [
 *       'values' => [
 *           self::ENUM_VALUE_1 => [
 *                'description' => 'Enum value 1',
 *           ],
 *           self::ENUM_VALUE_2 => [
 *                 'description' => 'Enum value 2',
 *                 'endpoints' => ['endpoint1', 'endpoint3'],
 *           ],
 *           self::ENUM_VALUE_3 => [
 *                  'description' => 'Enum value 3',
 *          ],
 *        ],
 *    ];
 *
 * parent::__construct($config);
 *
 *
 */
abstract class EnumType extends GraphQLEnumType implements EndpointAwareInterface, ContainerAwareInterface
{
    use EndpointAwareTrait;
    use ContainerAwareTrait;

    public function getValues(): array
    {
        if (isset($this->config['values'])) {
            foreach ($this->config['values'] as $value => $config) {
                if (isset($config['endpoints']) && !empty($config['endpoints'])) {
                    $endpoints = $this->endpointsAliasToRealNames($config['endpoints']);
                    if (!in_array($this->endpoint->getName(), $endpoints)) {
                        unset($this->config['values'][$value]);
                    }
                }
            }
        }

        return parent::getValues();
    }

    /**
     * Given array of endpoints (containing alias) return the array of specific endpoints (without aliases)
     *
     * ["all"] => ["admin", "frontend"]
     *
     * @param array $endpoints
     *
     * @return array
     */
    protected function endpointsAliasToRealNames($endpoints)
    {
        $endpointsAlias = $this->container->getParameter('graphql.endpoints')['alias'] ?? [];
        foreach ($endpoints as $index => $endpointName) {
            foreach ($endpointsAlias as $alias => $targets) {
                if ($alias === $endpointName) {
                    $targets = $this->endpointsAliasToRealNames($targets);
                    unset($endpoints[$index]);
                    $endpoints = array_merge($endpoints, $targets);
                }
            }
        }

        return $endpoints;
    }
}