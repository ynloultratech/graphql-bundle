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

/**
 * Add support  to use Date Function
 *
 * @example "{date('2010-01-01')}" => "DateTime instace for given date"
 * @example "{date()}" => current DateTime instance
 */
class DateProvider implements ExpressionPreprocessorInterface
{
    public function setUp(ExpressionLanguage $el, string &$expression, array &$values)
    {
        if ($expression && preg_match('/date\(/', $expression)) {
            $el->register(
                'date',
                function ($expression = 'now') {
                    return sprintf('$date(%s)', $expression);
                },
                function (array $variables, $expression = 'now') {
                    return $variables['date']($expression);
                }
            );

            $values['date'] = function ($expression = 'now') {
                return new \DateTime($expression);
            };
        }
    }
}
