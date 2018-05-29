<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\Assert;

use Symfony\Component\HttpFoundation\Response;

/**
 * @method Response getResponse()
 *
 * @requires JsonHelperTrait
 */
trait JsonAssertTrait
{
    /**
     * @param string|array $json
     * @param string       $type
     * @param string       $path
     */
    public static function assertJsonValueInternalType($json, $type, $path)
    {
        static::assertInternalType($type, static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param string       $type
     * @param string       $path
     */
    public static function assertJsonValueNotInternalType($json, $type, $path)
    {
        static::assertNotInternalType($type, static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param mixed        $expected
     * @param string       $path
     */
    public static function assertJsonValueEquals($json, $expected, $path)
    {
        static::assertEquals($expected, static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param string       $path
     */
    public static function assertJsonValueIsFalse($json, $path)
    {
        static::assertFalse(static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param string       $path
     */
    public static function assertJsonValueIsTrue($json, $path)
    {
        static::assertTrue(static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param mixed        $expected
     * @param string       $path
     */
    public static function assertJsonValueNotEquals($json, $expected, $path)
    {
        static::assertNotEquals($expected, static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param string       $path
     */
    public static function assertJsonValueIsNull($json, $path)
    {
        static::assertNull(static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param mixed        $expected
     * @param string       $path
     */
    public static function assertJsonArraySubset($json, $expected, $path)
    {
        static::assertArraySubset($expected, static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param string       $format
     * @param string       $path
     */
    public static function assertJsonValueMatchesFormat($json, $format, $path)
    {
        $value = static::getJsonPathValue($json, $path);
        static::assertStringMatchesFormat($format, $value);
    }

    /**
     * @param string|array $json
     * @param string       $format
     * @param string       $path
     */
    public static function assertJsonValueNotMatchesFormat($json, $format, $path)
    {
        $value = static::getJsonPathValue($json, $path);
        static::assertStringNotMatchesFormat($format, $value);
    }

    /**
     * @param string|array $json
     * @param string       $pattern
     * @param string       $path
     */
    public static function assertJsonValueRegExp($json, $pattern, $path)
    {
        $value = static::getJsonPathValue($json, $path);
        static::assertRegExp($pattern, $value);
    }

    /**
     * @param string|array $json
     * @param string       $pattern
     * @param string       $path
     */
    public static function assertJsonValueNotRegExp($json, $pattern, $path)
    {
        $value = static::getJsonPathValue($json, $path);
        static::assertNotRegExp($pattern, $value);
    }
}
