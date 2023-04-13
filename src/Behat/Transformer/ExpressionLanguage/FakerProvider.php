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

use Faker\Factory;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Add support to use faker as generator of fake values
 *
 * @require fzaninotto/faker
 *
 * @example "{faker.sentence()}" => "Consequatur quisquam recusandae asperiores accusamus nihil repellat."
 * @example "{faker.randomNumber()}" => 7086
 */
class FakerProvider implements ExpressionPreprocessorInterface
{
    public function setUp(ExpressionLanguage $el, string &$expression, array &$values)
    {
        if ($expression && preg_match('/faker\./', $expression)) {
            $faker = Factory::create();
            $faker->seed(1);
            $values['faker'] = $faker;
        }
    }
}
