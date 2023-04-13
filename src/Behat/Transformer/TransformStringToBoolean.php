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
 * Transform the world true and false to boolean,
 *
 * @example true => (bool) true
 * @example "true" => (bool) true
 * @example "false" => (bool) false
 *
 * @see     TransformAnyValueToString to scape types
 */
class TransformStringToBoolean implements ArgumentTransformer
{
    private const PATTERN = '/^true|false$/';

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return $argumentValue && preg_match(self::PATTERN, $argumentValue);
    }

    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return 'true' === $argumentValue ? true : false;
    }
}
