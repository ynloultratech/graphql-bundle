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

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Definition\ImplementorInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Resolver\DeferredBuffer;
use Ynlo\GraphQLBundle\Resolver\FieldExecutionContext;
use Ynlo\GraphQLBundle\Resolver\ObjectFieldResolver;
use Ynlo\GraphQLBundle\Resolver\QueryExecutionContext;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;
use Ynlo\GraphQLBundle\Util\GraphQLBuilder;

class ObjectDefinitionType extends ObjectType implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __construct(ObjectDefinition $definition)
    {
        parent::__construct(
            [
                'name' => $definition->getName(),
                'description' => $definition->getDescription(),
                'fields' => function () use ($definition) {
                    return GraphQLBuilder::resolveFields($definition);
                },
                'interfaces' => function () use ($definition) {
                    return $this->resolveInterfaces($definition);
                },
                'resolveField' => function ($root, array $args, QueryExecutionContext $context, ResolveInfo $resolveInfo) use ($definition) {
                    $resolver = new ObjectFieldResolver(
                        $this->container,
                        $this->container->get(DeferredBuffer::class)
                    );

                    return $resolver($root, $args, new FieldExecutionContext($context, $definition), $resolveInfo);
                }
            ]
        );
    }

    private function resolveInterfaces(ImplementorInterface $definition): array
    {
        $interfaces = [];
        foreach ($definition->getInterfaces() as $interface) {
            $interfaces[] = TypeRegistry::get($interface);
        }

        return $interfaces;
    }
}
