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
use Ynlo\GraphQLBundle\Behat\Fixtures\FixtureManager;

/**
 * Allow the use of fixtures inside expressions
 *
 * #fixtureName = to get the global id of existent fixture
 * @fixtureName = to get reference to given fixture name
 *
 * @example     "{@user1}" => User (object)
 * @example     "{[#user1, #user2]}" => ["VXNlcjox", "VXNlcjoy"]
 * @example     "{@user.getUsername()}" => "admin"
 */
class FixturePreprocessor implements ExpressionPreprocessorInterface
{
    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    public function __construct(FixtureManager $fixtureManager)
    {
        $this->fixtureManager = $fixtureManager;
    }

    public function setUp(ExpressionLanguage $el, string &$expression, array &$values)
    {
        // parse fixtures IDs
        preg_match_all('/#([\w\/\\-]+)/', $expression, $matches);
        if ($matches[0] ?? false) {
            foreach ($matches[0] as $index => $match) {
                $expression = preg_replace('/#([\w\/\\-]+)/', '$1_id', $expression);
                $values[$matches[1][$index].'_id'] = $this->fixtureManager->getFixtureGlobalId($matches[1][$index]);
            }
        }

        // parse fixtures references
        preg_match_all('/@([\w\/\\-]+)/', $expression, $matches);
        if ($matches[0] ?? false) {
            foreach ($matches[0] as $index => $match) {
                $expression = preg_replace('/@([\w\/\\-]+)/', '$1', $expression);
                $values[$matches[1][$index]] = $this->fixtureManager->getFixture($matches[1][$index]);
            }
        }
    }
}