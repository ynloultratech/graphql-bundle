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
    public static function assertJsonPathInternalType($json, $type, $path)
    {
        static::assertInternalType($type, static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param string       $type
     * @param string       $path
     */
    public static function assertJsonPathNotInternalType($json, $type, $path)
    {
        static::assertNotInternalType($type, static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param mixed        $expected
     * @param string       $path
     */
    public static function assertJsonPathEquals($json, $expected, $path)
    {
        static::assertEquals($expected, static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param string       $path
     */
    public static function assertJsonPathFalse($json, $path)
    {
        static::assertFalse(static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param string       $path
     */
    public static function assertJsonPathTrue($json, $path)
    {
        static::assertTrue(static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param mixed        $expected
     * @param string       $path
     */
    public static function assertJsonPathNotEquals($json, $expected, $path)
    {
        static::assertNotEquals($expected, static::getJsonPathValue($json, $path));
    }

    /**
     * @param string|array $json
     * @param string       $path
     */
    public static function assertJsonPathNull($json, $path)
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
     * @param string       $path
     */
    public static function assertJsonPathMatch($json, $path)
    {
        $value = static::getJsonPathValue($json, $path);
        if (\is_array($value)) {
            static::assertNotEmpty($value);
        } else {
            static::assertNotNull($value);
        }
    }

    /**
     * @param string|array $json
     * @param string       $path
     */
    public static function assertJsonPathNotMatch($json, $path)
    {
        $value = static::getJsonPathValue($json, $path);
        if (\is_array($value)) {
            static::assertEmpty($value);
        } else {
            static::assertNull($value);
        }
    }
}
