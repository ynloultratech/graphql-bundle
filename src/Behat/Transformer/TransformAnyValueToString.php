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
 * Transform any value to literal string,
 * can be used to escape scalar types like int or bool
 *
 * @example '2' => "2"
 * @example "'2'" => "2"
 * @example "'true'" => "true"
 * @example "'{faker.randomNumber}'" => "5531"
 */
class TransformAnyValueToString implements ArgumentTransformer
{
    private const PATTERN = '/^\'.*\'$/';

    /**
     * @var TransformStringToExpression
     */
    protected $expressionTransformer;

    /**
     * TransformAnyValueToString constructor.
     *
     * @param TransformStringToExpression $expressionTransformer
     */
    public function __construct(TransformStringToExpression $expressionTransformer)
    {
        $this->expressionTransformer = $expressionTransformer;
    }

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return preg_match(self::PATTERN, $argumentValue);
    }

    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        if (preg_match('/^\'({.*})\'$/', $argumentValue, $matches)) {
            return (string) $this->expressionTransformer->transformArgument($definitionCall, $argumentIndex, $matches[1]);
        }

        return (string) $argumentValue;
    }
}
