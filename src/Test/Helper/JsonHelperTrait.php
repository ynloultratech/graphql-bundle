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

use Symfony\Component\HttpFoundation\Response;
use function JmesPath\search;

/**
 * @method Response getResponse()
 *
 * @deprecated in favor of Behat tests
 */
trait JsonHelperTrait
{
    /**
     * @param string|array $json
     * @param string       $path
     *
     * @return mixed|null
     */
    public static function getJsonPathValue($json, string $path)
    {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }

        return search($path, $json);
    }
}
