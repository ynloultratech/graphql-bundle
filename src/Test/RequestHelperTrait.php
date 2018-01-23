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

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Client getClient()
 */
trait RequestHelperTrait
{
    /**
     * @param string $path
     * @param array  $parameters
     */
    protected static function sendGET($path, array $parameters = [])
    {
        static::getClient()->request(Request::METHOD_GET, $path, $parameters);
    }

    /**
     * @param string       $path
     * @param string|array $content
     */
    protected static function sendPOST($path, $content)
    {
        static::getClient()->request(Request::METHOD_POST, $path, [], [], [], $content);
    }

    /**
     * @param string       $path
     * @param string|array $content
     */
    protected static function sendPUT($path, $content)
    {
        static::getClient()->request(Request::METHOD_PUT, $path, [], [], [], $content);
    }

    /**
     * @param string $path
     */
    protected static function sendDELETE($path)
    {
        static::getClient()->request(Request::METHOD_DELETE, $path);
    }
}
