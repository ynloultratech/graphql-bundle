<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type;

use GraphQL\Type\Definition\Type as GraphQLType;

/**
 * Type
 */
final class Types
{
    public const ID = GraphQLType::ID;
    public const STRING = GraphQLType::STRING;
    public const INT = GraphQLType::INT;
    public const BOOLEAN = GraphQLType::BOOLEAN;
    public const FLOAT = GraphQLType::FLOAT;
    public const DATETIME = DateTimeType::DATETIME;
    public const DATE = DateType::DATE;
    public const TIME = TimeType::TIME;
    public const ANY = AnyType::ANY;
    public const DYNAMIC_OBJECT = DynamicObjectType::DYNAMIC_OBJECT;

    /**
     * @param string $type
     *
     * @return string
     */
    public static function listOf(string $type): string
    {
        return sprintf('[%s]', $type);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function nonNull(string $type): string
    {
        return sprintf('%s!', $type);
    }
}
