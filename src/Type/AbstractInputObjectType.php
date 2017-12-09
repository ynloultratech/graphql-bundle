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

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Resolver\ObjectFieldResolver;

/**
 * Class AbstractInputObjectType
 */
abstract class AbstractInputObjectType extends InputObjectType implements
    ContainerAwareInterface,
    DefinitionManagerAwareInterface
{
    use ContainerAwareTrait;
    use DefinitionManagerAwareTrait;

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
                'fields' => function () {
                    return $this->resolveFields();
                },
                'resolveField' => function ($root, array $args, $context, ResolveInfo $resolveInfo) {
                    $resolver = new ObjectFieldResolver($this->container, $this->manager, $this->definition);

                    return $resolver($root, $args, $context, $resolveInfo);
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
            if ($type instanceof ObjectType) {
                $type = Types::get($fieldDefinition->getType());
            }

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
            ];
        }

        return $fields;
    }
}
