<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Assert;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Util\Json;

/**
 * Set of asserts to check JSON values
 */
class AssertJson
{
    /**
     * @param string|array|Response $json
     * @param string                $type
     * @param string                $path
     */
    public static function assertValueInternalType($json, $type, $path)
    {
        Assert::assertInternalType($type, Json::getValue($json, $path));
    }

    /**
     * @param string|array|Response $json
     * @param string                $type
     * @param string                $path
     */
    public static function assertValueNotInternalType($json, $type, $path)
    {
        Assert::assertNotInternalType($type, Json::getValue($json, $path));
    }

    /**
     * @param string|array|Response $json
     * @param mixed                 $expected
     * @param string                $path
     */
    public static function assertValueEquals($json, $expected, $path)
    {
        Assert::assertEquals($expected, Json::getValue($json, $path));
    }

    /**
     * @param string|array|Response $json
     * @param string                $path
     */
    public static function assertValueIsFalse($json, $path)
    {
        Assert::assertFalse(Json::getValue($json, $path));
    }

    /**
     * @param string|array|Response $json
     * @param string                $path
     */
    public static function assertValueIsTrue($json, $path)
    {
        Assert::assertTrue(Json::getValue($json, $path));
    }

    /**
     * @param string|array|Response $json
     * @param mixed                 $expected
     * @param string                $path
     */
    public static function assertValueNotEquals($json, $expected, $path)
    {
        Assert::assertNotEquals($expected, Json::getValue($json, $path));
    }

    /**
     * @param string|array|Response $json
     * @param string                $path
     */
    public static function assertValueIsNull($json, $path)
    {
        Assert::assertNull(Json::getValue($json, $path));
    }

    /**
     * @param string|array|Response $json
     * @param mixed                 $expected
     * @param string                $path
     */
    public static function assertArraySubset($json, $expected, $path)
    {
        Assert::assertArraySubset($expected, Json::getValue($json, $path));
    }

    /**
     * @param string|array|Response $json
     * @param string                $format
     * @param string                $path
     */
    public static function assertValueMatchesFormat($json, $format, $path)
    {
        $value = Json::getValue($json, $path);
        Assert::assertStringMatchesFormat($format, $value);
    }

    /**
     * @param string|array|Response $json
     * @param string                $format
     * @param string                $path
     */
    public static function assertValueNotMatchesFormat($json, $format, $path)
    {
        $value = Json::getValue($json, $path);
        Assert::assertStringNotMatchesFormat($format, $value);
    }

    /**
     * @param string|array|Response $json
     * @param string                $pattern
     * @param string                $path
     */
    public static function assertValueRegExp($json, $pattern, $path)
    {
        $value = Json::getValue($json, $path);
        Assert::assertRegExp($pattern, $value);
    }

    /**
     * @param string|array|Response $json
     * @param string                $pattern
     * @param string                $path
     */
    public static function assertValueNotRegExp($json, $pattern, $path)
    {
        $value = Json::getValue($json, $path);
        Assert::assertNotRegExp($pattern, $value);
    }
}
