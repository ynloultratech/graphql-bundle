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
    public static function assertResponseJsonValueInternalType($type, $path)
    {
        static::assertInternalType($type, static::getResponseJsonPathValue($path));
    }

    /**
     * @param string $type
     * @param string $path
     */
    public static function assertResponseJsonValueNotInternalType($type, $path)
    {
        static::assertNotInternalType($type, static::getResponseJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    public static function assertResponseJsonValueEquals($expected, $path)
    {
        static::assertEquals($expected, static::getResponseJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    public static function assertResponseJsonValueIsFalse($path)
    {
        static::assertFalse(static::getResponseJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    public static function assertResponseJsonValueIsTrue($path)
    {
        static::assertTrue(static::getResponseJsonPathValue($path));
    }

    /**
     * @param mixed  $expected
     * @param string $path
     */
    public static function assertResponseJsonValueNotEquals($expected, $path)
    {
        static::assertNotEquals($expected, static::getResponseJsonPathValue($path));
    }

    /**
     * @param string $path
     */
    public static function assertResponseJsonValueIsNull($path)
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
     * @param string $format
     * @param string $path
     */
    public static function assertResponseJsonValueMatchesFormat($format, $path)
    {
        static::assertJsonValueMatchesFormat(static::getResponse()->getContent(), $format, $path);
    }

    /**
     * @param string $format
     * @param string $path
     */
    public static function assertResponseJsonValueNotMatchesFormat($format, $path)
    {
        static::assertJsonValueNotMatchesFormat(static::getResponse()->getContent(), $format, $path);
    }

    /**
     * @param string $pattern
     * @param string $path
     */
    public static function assertResponseJsonValueRegExp($pattern, $path)
    {
        static::assertJsonValueRegExp(static::getResponse()->getContent(), $pattern, $path);
    }

    /**
     * @param string $pattern
     * @param string $path
     */
    public static function assertResponseJsonValueNotRegExp($pattern, $path)
    {
        static::assertJsonValueNotRegExp(static::getResponse()->getContent(), $pattern, $path);
    }
}
