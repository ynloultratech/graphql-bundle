<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type\Definition;

use GraphQL\Type\Definition\InterfaceType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Util\GraphQLBuilder;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Class InterfaceDefinitionType
 */
class InterfaceDefinitionType extends InterfaceType implements EndpointAwareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use EndpointAwareTrait;

    /**
     * @var InterfaceDefinition
     */
    protected $definition;

    /**
     * InterfaceDefinitionType constructor.
     *
     * @param InterfaceDefinition $definition
     */
    public function __construct(InterfaceDefinition $definition)
    {
        $this->definition = $definition;

        parent::__construct(
            [
                'name' => $definition->getName(),
                'description' => $definition->getDescription(),
                'fields' => function () use ($definition) {
                    return GraphQLBuilder::resolveFields($definition);
                },
                'resolveType' => function ($value) {
                    return TypeUtil::resolveNodeType($this->endpoint, $value);
                },
            ]
        );
    }
}
