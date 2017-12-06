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

use Doctrine\Common\Util\ClassUtils;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;

/**
 * Class AbstractInterfaceType
 */
abstract class AbstractInterfaceType extends InterfaceType
{
    /**
     * @var InterfaceDefinition
     */
    protected $definition;

    /**
     * @var DefinitionManager
     */
    protected $definitionManager;

    /**
     * AbstractInterfaceType constructor.
     *
     * @param DefinitionManager   $definitionManager
     * @param InterfaceDefinition $definition
     */
    public function __construct(DefinitionManager $definitionManager, InterfaceDefinition $definition)
    {
        $this->definition = $definition;
        $this->definitionManager = $definitionManager;

        parent::__construct(
            [
                'name' => $definition->getName(),
                'description' => $definition->getDescription(),
                'fields' => function () {
                    return $this->resolveFields();
                },
                'resolveType' => function ($value) {
                    foreach ($this->definition->getImplementors() as $implementor) {
                        $implementorDef = $this->definitionManager->getType($implementor);
                        //ClassUtils::getClass is required to avoid proxies
                        if ($implementorDef->getClass() === ClassUtils::getClass($value)) {
                            return Types::get($implementorDef->getName());
                        }
                    }

                    return null;
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
}
