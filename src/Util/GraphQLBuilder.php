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
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Ynlo\GraphQLBundle\Definition\ArgumentAwareInterface;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;

class GraphQLBuilder
{
    public static function buildArguments(ArgumentAwareInterface $argumentAware): array
    {
        $args = [];
        foreach ($argumentAware->getArguments() as $argDefinition) {
            $arg = [];
            $arg['description'] = $argDefinition->getDescription();
            $argType = TypeRegistry::get($argDefinition->getType());

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

    public static function buildComplexityFn(?string $complexity): ?callable
    {
        if (null === $complexity) {
            return null;
        }

        if (is_numeric($complexity)) {
            return function ($childrenComplexity) use ($complexity) {
                return $childrenComplexity + $complexity;
            };
        }

        // support only static string callable func
        if (\is_string($complexity) && \is_callable($complexity)) {
            return $complexity;
        }

        $el = new ExpressionLanguage();

        return function ($childrenComplexity, $args) use ($el, $complexity) {
            return $el->evaluate($complexity, array_merge($args, ['children_complexity' => $childrenComplexity]));
        };
    }
}
