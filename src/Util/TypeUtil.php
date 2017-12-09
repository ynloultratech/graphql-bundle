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

use GraphQL\Type\Definition\Type;

/**
 * Util to work with GraphQL types
 */
final class TypeUtil
{
    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isTypeList($type): bool
    {
        return (bool) preg_match('/^\[([\\\\\w]+)!?\]!?$/', $type);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isTypeNonNullList($type): bool
    {
        return (bool) preg_match('/^\[([\\\\\w]+)!\]!?$/', $type);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isTypeNonNull($type): bool
    {
        return (bool) preg_match('/!$/', $type);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function normalize($type)
    {
        if (preg_match('/^\[?([\\\\\w]+)!?\]?!?$/', $type, $matches)) {
            $type = $matches[1];
        }

        switch ($type) {
            case 'bool':
            case 'boolean':
                $type = Type::BOOLEAN;
                break;
            case 'decimal':
            case 'float':
                $type = Type::FLOAT;
                break;
            case 'int':
            case 'integer':
                $type = Type::INT;
                break;
            case 'string':
                $type = Type::STRING;
                break;
            case 'id':
                $type = Type::ID;
                break;
            case 'datetime':
            case 'date_time':
            case 'date':
                $type = 'DateTime';
                break;
        }

        return $type;
    }
}
