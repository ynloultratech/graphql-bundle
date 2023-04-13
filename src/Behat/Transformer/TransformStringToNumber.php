<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Transformer;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Transformer\ArgumentTransformer;

/**
 * Transform a number to string,
 * Any number along inside a parameter is considered a number
 *
 * @example 1 => (int) 1
 * @example "1" => (int) 1
 *
 * @see     TransformAnyValueToString to scape types
 */
class TransformStringToNumber implements ArgumentTransformer
{
    private const PATTERN = '/^(\d+)$/';

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return $argumentValue && preg_match(self::PATTERN, $argumentValue);
    }

    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return (int) $argumentValue;
    }
}
