<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Context;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Ynlo\GraphQLBundle\Behat\Gherkin\YamlStringNode;

/**
 * A set of assertion methods.
 */
final class AssertContext implements Context
{
    /**
     * Compare equality of two values.
     *
     * Example: Then "{response.data.title}" should be equal to "Welcome"
     * Example: Then "{response.data.title}" should be equal to "{@post1.getTitle()}"
     * Example: Then "{response.data.id}" should be equal to "#post1"
     *
     * @Then /^"([^"]*)" should be equal to "([^"]*)"$/
     */
    public function shouldBeEqualTo($actual, $expected)
    {
        Assert::assertEquals($expected, $actual);
    }

    /**
     * Compare NOT equality of two values.
     *
     * Example: Then "{response.data.title}" should not be equal to "Welcome"
     * Example: Then "{response.data.title}" should not be equal to "{@post1.getTitle()}"
     * Example: Then "{response.data.id}" should not be equal to "#post1"
     *
     * @Then /^"([^"]*)" should not be equal to "([^"]*)"$/
     */
    public function shouldNotBeEqualTo($actual, $expected)
    {
        Assert::assertNotEquals($expected, $actual);
    }

    /**
     * Asserts that a value is greater than another value.
     *
     * Example: Then "{response.data.amount}" should be greater than 20
     *
     * @Then /^"([^"]*)" should be greater than ([^"]*)$/
     */
    public function shouldBeGreaterThan($actual, $expected)
    {
        Assert::assertGreaterThan($expected, $actual);
    }

    /**
     *Asserts that a value is greater than or equal to another value.
     *
     * Example: Then "{response.data.amount}" should be greater or equal to 20
     *
     * @Then /^"([^"]*)" should be greater or equal to ([^"]*)$/
     */
    public function shouldBeGreaterOrEqual($actual, $expected)
    {
        Assert::assertGreaterThanOrEqual($expected, $actual);
    }

    /**
     * Asserts that a value is less than another value.
     *
     * Example: Then "{response.data.amount}" should be less than 20
     *
     * @Then /^"([^"]*)" should be less than ([^"]*)$/
     */
    public function shouldBeLessThan($actual, $expected)
    {
        Assert::assertLessThan($expected, $actual);
    }

    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * Example: Then "{response.data.amount}" should be greater or equal to 20
     *
     * @Then /^"([^"]*)" should be less or equal to ([^"]*)$/
     */
    public function shouldBeLessOrEqual($actual, $expected)
    {
        Assert::assertLessThanOrEqual($expected, $actual);
    }

    /**
     * Asserts that a condition is true.
     *
     * Example: Then "{response.data.enabled}" should be true
     *
     * @Then /^"([^"]*)" should be true$/
     */
    public function shouldBeTrue($value)
    {
        Assert::assertTrue($value);
    }

    /**
     * Asserts that a condition is false.
     *
     * Example: Then "{response.data.enabled}" should be false
     *
     * @Then /^"([^"]*)" should be false$/
     */
    public function shouldBeFalse($actual)
    {
        Assert::assertFalse($actual);
    }

    /**
     * Asserts that a value is empty
     *
     * Example: Then "{response.data.body}" should be empty
     *
     * @Then /^"([^"]*)" should be empty$/
     */
    public function shouldBeEmpty($value)
    {
        Assert::assertEmpty($value);
    }

    /**
     * Asserts that a value is NOT empty
     *
     * Example: Then "{response.data.title}" should not be empty
     *
     * @Then /^"([^"]*)" should not be empty$/
     */
    public function shouldNotBeEmpty($value)
    {
        Assert::assertNotEmpty($value);
    }

    /**
     * Asserts that a value is null
     *
     * Example: Then "{response.data.title}" should be null
     *
     * @Then /^"([^"]*)" should be null$/
     */
    public function shouldBeNull($value)
    {
        Assert::assertNull($value);
    }

    /**
     * Asserts that a value is NOT null
     *
     * Example: Then "{response.data.title}" should not be null
     *
     * @Then /^"([^"]*)" should not be null$/
     */
    public function shouldNotBeNull($value)
    {
        Assert::assertNotNull($value);
    }

    /**
     * Asserts that a condition match Then expression
     *
     * Example: Then "{response.data.number}" should match /^\d+$/
     *
     * @Then /^"([^"]*)" should match "([^"]*)"$/
     */
    public function shouldMatchRegExp($value, $exp)
    {
        Assert::assertRegExp($exp, $value);
    }

    /**
     * Asserts that a condition NOT match Then expression
     *
     * Example: Then "{response.data.number}" should not match /^\w+$/
     *
     * @Then /^"([^"]*)" should not match "([^"]*)"$/
     */
    public function shouldNotMatchRegExp($value, $exp)
    {
        Assert::assertNotRegExp($exp, $value);
    }

    /**
     * Asserts that a haystack contains a needle.
     *
     * Example: Then "{response.data.tags}" should contains "php"
     *
     * @Then /^"([^"]*)" should contains "([^"]*)"$/
     */
    public function shouldContains($haystack, $needle)
    {
        Assert::assertContains($needle, $haystack);
    }

    /**
     * Asserts that a haystack NOT contains a needle.
     *
     * Example: Then "{response.data.tags}" should not contains "javascript"
     *
     * @Then /^"([^"]*)" should not contains "([^"]*)"$/
     */
    public function shouldNotContains($haystack, $needle)
    {
        Assert::assertNotContains($needle, $haystack);
    }

    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * Example: Then "{response.data.tags}" should have "3" items
     *
     * @Then /^"([^"]*)" should have ([^"]*) items$/
     */
    public function shouldCountElements($haystack, $count)
    {
        Assert::assertCount($count, $haystack);
    }

    /**
     * Asserts NOT the number of elements of an array, Countable or Traversable.
     *
     * Example: Then "{response.data.tags}" should don't have "3" items
     *
     * @Then /^"([^"]*)" should don't have ([^"]*) items$/
     */
    public function shouldNotCountElements($haystack, $count)
    {
        Assert::assertNotCount($count, $haystack);
    }

    /**
     * Asserts that an array has a specified subset.
     *
     * Example:  And "{search('data.posts[*].title', response)}" should contains this subset:
     *           """
     *           - Welcome
     *           - "{@post1.getTitle()}"
     *           """
     *
     * @Then /^"([^"]*)" should contains this subset:$/
     */
    public function shouldContainsASubset($actual, YamlStringNode $subset)
    {
        Assert::assertArraySubset($subset->toArray(), $actual);
    }
}
