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
    public const ID = GraphQLType ::ID;
    public const STRING = GraphQLType::STRING;
    public const INT = GraphQLType::INT;
    public const BOOLEAN = GraphQLType::BOOLEAN;
    public const FLOAT = GraphQLType::FLOAT;
    public const DATE_TIME = 'DateTime';

    /**
     * @param string $type
     *
     * @return string
     */
    public static function listOf(string $type): string
    {
        return sprintf('[%s]', $type);
    }
}
