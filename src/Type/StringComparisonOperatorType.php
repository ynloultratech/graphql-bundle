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

use GraphQL\Type\Definition\EnumType;

class StringComparisonOperatorType extends EnumType
{
    public const CONTAINS = 'CONTAINS';
    public const EQUAL = 'EQUAL';
    public const STARTS_WITH = 'STARTS_WITH';
    public const ENDS_WITH = 'ENDS_WITH';

    public function __construct()
    {
        $config = [
            'name' => 'StringComparisonOperator',
            'values' => [
                self::CONTAINS => [],
                self::EQUAL => [],
                self::STARTS_WITH => [],
                self::ENDS_WITH => [],
            ],
        ];

        parent::__construct($config);
    }
}
