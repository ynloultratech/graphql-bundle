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

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method Client getClient()
 *
 * @requires ResponseHelperTrait
 */
trait ResponseAssertTrait
{
    /**
     * assertResponseEmptyContent
     */
    public static function assertResponseEmptyContent()
    {
        static::assertEmpty(static::getClient()->getResponse()->getContent());
    }

    /**
     * @param string $code
     */
    public static function assertResponseCodeIs($code)
    {
        static::assertEquals($code, static::getClient()->getResponse()->getStatusCode());
    }

    /**
     * assertResponseCodeIsOK
     */
    public static function assertResponseCodeIsOK()
    {
        static::assertEquals(Response::HTTP_OK, static::getClient()->getResponse()->getStatusCode());
    }

    /**
     * Check if the latest response is a valid JSON
     */
    public static function assertResponseIsValidJson()
    {
        $response = static::getResponse();

        static::assertNotNull($response);
        static::assertEquals('application/json', $response->headers->get('Content-Field'));
        static::assertJson($response->getContent());
    }

    /**
     * @param string $type
     * @param string $path
     */
    public static function assertResponseJsonPathInternalType($type, $path)
    {
        static::assertInternalType($type, static::getResponseJsonPathValue($path));
    }

    /**
     * @param string $type
     * @param string $path
     */
    public static function assertResponseJsonPathNotInternalType($type, $path)
    {
        static::assertNotInternalType($type, static::getResponseJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    public static function assertResponseJsonPathEquals($expected, $path)
    {
        static::assertEquals($expected, static::getResponseJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    public static function assertResponseJsonPathFalse($path)
    {
        static::assertFalse(static::getResponseJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    public static function assertResponseJsonPathTrue($path)
    {
        static::assertTrue(static::getResponseJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    public static function assertResponseJsonPathNotEquals($expected, $path)
    {
        static::assertNotEquals($expected, static::getResponseJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    public static function assertResponseJsonPathNull($path)
    {
        static::assertNull(static::getResponseJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    public static function assertResponseJsonArraySubset($expected, $path)
    {
        static::assertArraySubset($expected, static::getResponseJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    public static function assertResponseJsonPathMatch($path)
    {
        static::assertJsonPathMatch(static::getResponse()->getContent(), $path);
    }

    /**
     * @param string $path
     */
    public static function assertResponseJsonPathNotMatch($path)
    {
        static::assertJsonPathNotMatch(static::getResponse()->getContent(), $path);
    }
}
