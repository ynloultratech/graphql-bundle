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

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method Client getClient()
 */
trait ResponseHelperTrait
{
    /**
     * assertResponseEmptyContent
     */
    protected static function assertResponseEmptyContent()
    {
        self::assertEmpty(self::getClient()->getResponse()->getContent());
    }

    /**
     * @param string $code
     */
    protected static function assertResponseCodeIs($code)
    {
        self::assertEquals($code, self::getClient()->getResponse()->getStatusCode());
    }

    /**
     * assertResponseCodeIsOK
     */
    protected static function assertResponseCodeIsOK()
    {
        self::assertEquals(Response::HTTP_OK, self::getClient()->getResponse()->getStatusCode());
    }

    /**
     * @return Response
     */
    protected static function getResponse(): Response
    {
        return self::getClient()->getResponse();
    }

    /**
     * dumpResponse
     */
    protected static function dumpResponse()
    {
        $content = self::getClient()->getResponse()->getContent();
        $json = @json_decode($content, true);
        if ($json) {
            print_r($json);
        } else {
            print_r($content);
        }
    }
}
