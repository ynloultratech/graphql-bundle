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

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Resolver\FieldExecutionContext;
use Ynlo\GraphQLBundle\Resolver\ObjectFieldResolver;
use Ynlo\GraphQLBundle\Resolver\QueryExecutionContext;
use Ynlo\GraphQLBundle\Util\GraphQLBuilder;

/**
 * Class InputObjectDefinitionType
 */
class InputObjectDefinitionType extends InputObjectType implements
    ContainerAwareInterface,
    EndpointAwareInterface
{
    use ContainerAwareTrait;
    use EndpointAwareTrait;

    /**
     * @var InputObjectDefinition
     */
    protected $definition;

    /**
     * @param InputObjectDefinition $definition
     */
    public function __construct(InputObjectDefinition $definition)
    {
        $this->definition = $definition;

        parent::__construct(
            [
                'name' => $definition->getName(),
                'description' => $definition->getDescription(),
                'fields' => function () use ($definition) {
                    return GraphQLBuilder::resolveFields($definition);
                },
                'resolveField' => function ($root, array $args, QueryExecutionContext $context, ResolveInfo $resolveInfo) use ($definition) {
                    $resolver = new ObjectFieldResolver($this->container);

                    return $resolver($root, $args, new FieldExecutionContext($context, $definition), $resolveInfo);
                },
            ]
        );
    }
}
