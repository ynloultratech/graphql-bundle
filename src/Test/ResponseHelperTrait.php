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
        static::assertEmpty(static::getClient()->getResponse()->getContent());
    }

    /**
     * @param string $code
     */
    protected static function assertResponseCodeIs($code)
    {
        static::assertEquals($code, static::getClient()->getResponse()->getStatusCode());
    }

    /**
     * assertResponseCodeIsOK
     */
    protected static function assertResponseCodeIsOK()
    {
        static::assertEquals(Response::HTTP_OK, static::getClient()->getResponse()->getStatusCode());
    }

    /**
     * @return Response
     */
    protected static function getResponse(): Response
    {
        return static::getClient()->getResponse();
    }
}
