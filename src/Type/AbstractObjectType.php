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
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectFieldResolver;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;

/**
 * Class AbstractObjectType
 */
abstract class AbstractObjectType extends ObjectType
{
    /**
     * @var ObjectDefinition
     */
    protected $definition;

    /**
     * @var DefinitionManager
     */
    protected $definitionManager;

    /**
     * AbstractObjectType constructor.
     *
     * @param DefinitionManager $definitionManager
     * @param ObjectDefinition  $definition
     */
    public function __construct(DefinitionManager $definitionManager, ObjectDefinition $definition)
    {
        $this->definitionManager = $definitionManager;
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
                'resolveField' => new ObjectFieldResolver($definition),
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
