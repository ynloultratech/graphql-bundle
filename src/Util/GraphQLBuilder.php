<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Util;

use GraphQL\Type\Definition\Type;
use Ynlo\GraphQLBundle\Definition\ArgumentAwareInterface;
use Ynlo\GraphQLBundle\Type\Types;

/**
 * GraphQLBuilder
 */
class GraphQLBuilder
{
    /**
     * @param ArgumentAwareInterface $argumentAware
     *
     * @return array
     */
    public static function buildArguments(ArgumentAwareInterface $argumentAware)
    {
        $args = [];
        foreach ($argumentAware->getArguments() as $argDefinition) {
            $arg = [];
            $arg['description'] = $argDefinition->getDescription();
            $argType = Types::get($argDefinition->getType());

            if ($argDefinition->isList()) {
                if ($argDefinition->isNonNullList()) {
                    $argType = Type::nonNull($argType);
                }
                $argType = Type::listOf($argType);
            }

            if ($argDefinition->isNonNull()) {
                $argType = Type::nonNull($argType);
            }

            $arg['type'] = $argType;
            if ($argDefinition->getDefaultValue()) {
                $arg['defaultValue'] = $argDefinition->getDefaultValue();
            }
            $args[$argDefinition->getName()] = $arg;
        }

        return $args;
    }
}
