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

class Uuid
{
    /**
     * Create universal identifier for given data.
     * Helpful to create unique across then system, but the same when use the same data
     *
     * @param mixed $data
     *
     * @return string
     */
    public static function createFromData($data)
    {
        if (\is_array($data) || \is_object($data)) {
            $data = serialize($data);
        }

        $hash = strtoupper(md5($data));

        return implode(
            '-',
            [
                substr($hash, 0, 8),
                substr($hash, 8, 4),
                substr($hash, 12, 4),
                substr($hash, 16, 4),
                substr($hash, 20, 8),
            ]
        );
    }
}
