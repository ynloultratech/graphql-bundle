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

use GraphQL\Type\Definition\EnumType as BaseEnumType;
use Ynlo\GraphQLBundle\Definition\EnumDefinition;

/**
 * Class AbstractObjectType
 */
abstract class EnumType extends BaseEnumType
{
    /**
     * @param EnumDefinition $definition
     */
    public function __construct(EnumDefinition $definition)
    {
        $values = [];
        foreach ($definition->getValues() as $value) {
            $name = $value->getName();
            $values[$name] = [
                'value' => $value->getValue(),
                'description' => $value->getDescription(),
                'deprecationReason' => $value->getDeprecationReason(),
            ];
        }
        parent::__construct(
            [
                'name' => $definition->getName(),
                'description' => $definition->getDescription(),
                'values' => $values,
            ]
        );
    }
}
