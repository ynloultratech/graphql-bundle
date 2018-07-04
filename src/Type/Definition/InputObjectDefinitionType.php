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
use Ynlo\GraphQLBundle\Resolver\ContextBuilder;
use Ynlo\GraphQLBundle\Resolver\ObjectFieldResolver;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;
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
                'resolveField' => function ($root, array $args, ResolverContext $context, ResolveInfo $resolveInfo) use ($definition) {
                    $resolver = new ObjectFieldResolver($this->container);
                    $context = ContextBuilder::create($context->getEndpoint())
                                             ->setRoot($root)
                                             ->setResolveInfo($resolveInfo)
                                             ->setArgs($args)
                                             ->setDefinition($definition->getField($resolveInfo->fieldName))
                                             ->build();


                    return $resolver($root, $args, $context, $resolveInfo);
                },
            ]
        );
    }
}
