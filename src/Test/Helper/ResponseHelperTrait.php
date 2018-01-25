<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\Helper;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method Client getClient()
 *
 * @requires JsonHelperTrait
 */
trait ResponseHelperTrait
{
    public static function getResponse(): Response
    {
        return static::getClient()->getResponse();
    }

    public static function getResponseJsonArray(): array
    {
        return json_decode(static::getResponse()->getContent(), true);
    }

    /**
     * @param string $path
     *
     * @return mixed|null
     */
    public static function getResponseJsonPathValue(string $path)
    {
        return static::getJsonPathValue(static::getResponseJsonArray(), $path);
    }
}
