<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Util;

use Symfony\Component\HttpFoundation\Response;
use function JmesPath\search;

/**
 * JSON Utility class to work with json
 */
class Json
{
    /**
     * Get value inside given json using expression path
     *
     * Example: getValue($json, 'user.username')
     * Example: getValue($json, 'pos[0].title')
     *
     * @see `JMESPath Tutorial <http://jmespath.org/tutorial.html>
     * @see `JMESPath Grammar <http://jmespath.org/specification.html#grammar>
     *
     * @param string|array|Response $json
     * @param string                $path
     *
     * @return mixed|null
     */
    public static function getValue($json, string $path)
    {
        return search($path, Json::decode($json));
    }

    /**
     * Decode a json, unlike the build-in
     * decode, support a Response as argument
     *
     * @param string|array|Response $json
     * @param boolean               $assoc
     *
     * @return mixed
     */
    public static function decode($json, $assoc = false)
    {
        if ($json instanceof Response) {
            return json_decode($json->getContent(), $assoc);
        }

        return json_decode($json, $assoc);
    }
}
