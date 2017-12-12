<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Resolver\ObjectFieldResolver;
use Ynlo\GraphQLBundle\Util\GraphQLBuilder;

/**
 * Class AbstractObjectType
 */
abstract class AbstractObjectType extends ObjectType implements
    ContainerAwareInterface,
    EndpointAwareInterface
{
    use ContainerAwareTrait;
    use EndpointAwareTrait;

    /**
     * @var ObjectDefinition
     */
    protected $definition;

    /**
     * @param ObjectDefinition $definition
     */
    public function __construct(ObjectDefinition $definition)
    {
        $this->definition = $definition;

        parent::__construct(
            [
                'name' => $definition->getName(),
                'description' => $definition->getDescription(),
                'fields' => function () {
                    return $this->resolveFields();
                },
                'interfaces' => function () {
                    return $this->resolveInterfaces();
                },
                'resolveField' => function ($root, array $args, $context, ResolveInfo $resolveInfo) {
                    $resolver = new ObjectFieldResolver($this->container, $this->endpoint, $this->definition);

                    return $resolver($root, $args, $context, $resolveInfo);
                },
                'isTypeOf' => function ($value, $context, ResolveInfo $info) {
                    //TODO: implement this
                },
            ]
        );
    }

    /**
     * @return array
     */
    private function resolveFields()
    {
        $fields = [];
        foreach ($this->definition->getFields() as $fieldDefinition) {
            $type = Types::get($fieldDefinition->getType());

            if ($fieldDefinition->isList()) {
                if ($fieldDefinition->isNonNullList()) {
                    $type = Type::nonNull($type);
                }
                $type = Type::listOf($type);
            }

            if ($fieldDefinition->isNonNull()) {
                $type = Type::nonNull($type);
            }

            $fields[$fieldDefinition->getName()] = [
                'type' => $type,
                'description' => $fieldDefinition->getDescription(),
                'deprecationReason' => $fieldDefinition->getDeprecationReason(),
                'args' => GraphQLBuilder::buildArguments($fieldDefinition),
            ];
        }

        return $fields;
    }

    /**
     * @return array
     */
    private function resolveInterfaces()
    {
        $interfaces = [];
        foreach ($this->definition->getInterfaces() as $interface) {
            $interfaces[] = Types::get($interface);
        }

        return $interfaces;
    }
}
