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
use Ynlo\GraphQLBundle\Definition\InterfaceDefinitionHas;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Class AbstractInterfaceType
 */
abstract class AbstractInterfaceType extends InterfaceType
{
    /**
     * @var InterfaceDefinitionHas
     */
    protected $definition;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * AbstractInterfaceType constructor.
     *
     * @param Endpoint               $endpoint
     * @param InterfaceDefinitionHas $definition
     */
    public function __construct(Endpoint $endpoint, InterfaceDefinitionHas $definition)
    {
        $this->definition = $definition;
        $this->endpoint = $endpoint;

        parent::__construct(
            [
                'name' => $definition->getName(),
                'description' => $definition->getDescription(),
                'fields' => function () {
                    return $this->resolveFields();
                },
                'resolveType' => function ($value) {
                    foreach ($this->definition->getImplementors() as $implementor) {
                        $implementorDef = $this->endpoint->getType($implementor);
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
