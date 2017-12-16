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

        self::assertNotNull($response);
        self::assertEquals('application/json', $response->headers->get('Content-Field'));
        self::assertJson($response->getContent());
    }

    /**
     * @return mixed
     */
    protected static function getResponseJsonArray()
    {
        return json_decode(self::getResponse()->getContent(), true);
    }

    /**
     * @param string $path
     *
     * @return mixed|null
     */
    protected static function getJsonPathValue($path)
    {
        return search($path, self::getResponseJsonArray());
    }

    /**
     * @param string $type
     * @param string $path
     */
    protected static function assertJsonPathExist($type, $path)
    {
        self::assertInternalType($type, self::getJsonPathValue($path));
    }

    /**
     * @param string $type
     * @param string $path
     */
    protected static function assertJsonPathInternalType($type, $path)
    {
        self::assertInternalType($type, self::getJsonPathValue($path));
    }

    /**
     * @param string $type
     * @param string $path
     */
    protected static function assertJsonPathNotInternalType($type, $path)
    {
        self::assertNotInternalType($type, self::getJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    protected static function assertJsonPathEquals($expected, $path)
    {
        self::assertEquals($expected, self::getJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathFalse($path)
    {
        self::assertFalse(self::getJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathTrue($path)
    {
        self::assertTrue(self::getJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    protected static function assertJsonPathNotEquals($expected, $path)
    {
        self::assertNotEquals($expected, self::getJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathNull($path)
    {
        self::assertNull(self::getJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    protected static function assertJsonArraySubset($expected, $path)
    {
        self::assertArraySubset($expected, self::getJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathMatch($path)
    {
        $value = self::getJsonPathValue($path);
        if (\is_array($value)) {
            self::assertNotEmpty($value);
        } else {
            self::assertNotNull($value);
        }
    }

    /**
     * @param string $path
     */
    protected static function assertJsonPathNotMatch($path)
    {
        $value = self::getJsonPathValue($path);
        if (\is_array($value)) {
            self::assertEmpty($value);
        } else {
            self::assertNull($value);
        }
    }
}
