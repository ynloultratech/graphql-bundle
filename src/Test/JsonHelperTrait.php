<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test;

use Symfony\Component\HttpFoundation\Response;
use function JmesPath\search;

/**
 * Trait JsonHelperTrait
 *
 * @method Response getResponse()
 */
trait JsonHelperTrait
{
    /**
     * Check if the latest response is a valid JSON
     */
    protected static function assertResponseIsValidJson()
    {
        $response = self::getResponse();

        static::assertNotNull($response);
        static::assertEquals('application/json', $response->headers->get('Content-Field'));
        static::assertJson($response->getContent());
    }

    /**
     * @return mixed
     */
    protected static function getResponseJsonArray()
    {
        return json_decode(static::getResponse()->getContent(), true);
    }

    /**
     * @param string $path
     *
     * @return mixed|null
     */
    protected static function getJsonPathValue($path)
    {
        return search($path, static::getResponseJsonArray());
    }

    /**
     * @param string $type
     * @param string $path
     */
    protected static function assertJsonPathExist($type, $path)
    {
        static::assertInternalType($type, static::getJsonPathValue($path));
    }

    /**
     * @param string $type
     * @param string $path
     */
    protected static function assertJsonPathInternalType($type, $path)
    {
        static::assertInternalType($type, static::getJsonPathValue($path));
    }

    /**
     * @param string $type
     * @param string $path
     */
    protected static function assertJsonPathNotInternalType($type, $path)
    {
        static::assertNotInternalType($type, static::getJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    protected static function assertJsonPathEquals($expected, $path)
    {
        static::assertEquals($expected, static::getJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathFalse($path)
    {
        static::assertFalse(static::getJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathTrue($path)
    {
        static::assertTrue(static::getJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    protected static function assertJsonPathNotEquals($expected, $path)
    {
        static::assertNotEquals($expected, static::getJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathNull($path)
    {
        static::assertNull(static::getJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    protected static function assertJsonArraySubset($expected, $path)
    {
        static::assertArraySubset($expected, static::getJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathMatch($path)
    {
        $value = static::getJsonPathValue($path);
        if (\is_array($value)) {
            static::assertNotEmpty($value);
        } else {
            static::assertNotNull($value);
        }
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathNotMatch($path)
    {
        $value = static::getJsonPathValue($path);
        if (\is_array($value)) {
            static::assertEmpty($value);
        } else {
            static::assertNull($value);
        }
    }
}
