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
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Ynlo\GraphQLBundle\Behat\Transformer\ExpressionLanguage\ExpressionPreprocessorInterface;

/**
 * Allow the use of expressions (Symfony Expression Language) inside any argument
 * The expression must be placed inside {...}
 *
 * Use preprocessors to improve expressions.
 *
 * @see Ynlo\GraphQLBundle\Behat\Transformer\ExpressionLanguage\*
 */
class TransformStringToExpression implements ArgumentTransformer
{
    private const PATTERN = '/^{.*}$/';

    /**
     * @var ExpressionPreprocessorInterface[]
     */
    protected $preprocessors = [];

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return $argumentValue && preg_match(self::PATTERN, $argumentValue);
    }

    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        $values = [];
        $expression = $argumentValue;
        $el = new ExpressionLanguage();

        foreach ($this->preprocessors as $preprocessor) {
            $preprocessor->setUp($el, $expression, $values);
        }

        $expression = preg_replace('/^{(.*)}$/', '$1', $expression);

        return $el->evaluate($expression, $values);
    }

    public function registerPreprocessor(ExpressionPreprocessorInterface $preprocessor)
    {
        $this->preprocessors[] = $preprocessor;
    }
}
