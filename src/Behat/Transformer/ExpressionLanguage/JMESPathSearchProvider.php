<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Transformer\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use function JmesPath\search;

/**
 * Add support to search using JMESPath expressions
 *
 * @require mtdowling/jmespath.php
 *
 * @example "{ search('data.items[*].title', response) }" => ["Title1", "Title2", "Title3"]
 *
 * @see     http://jmespath.org/examples.html
 * @see     http://jmespath.org/tutorial.html
 */
class JMESPathSearchProvider implements ExpressionPreprocessorInterface
{
    public function setUp(ExpressionLanguage $el, string &$expression, array &$values)
    {
        if ($expression && preg_match('/search\(/', $expression)) {
            $el->register(
                'search',
                function ($expression, $data) {
                    return sprintf('$search(%s, %s)', $expression, $data);
                },
                function (array $variables, $expression, $data) {
                    return $variables['search']($expression, $data);
                }
            );

            $values['search'] = function ($expression, $data) {
                return search($expression, $data);
            };
        }
    }
}