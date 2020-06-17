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

class DateComparisonOperatorType extends EnumType
{
    public const BEFORE = 'BEFORE';
    public const AFTER = 'AFTER';
    public const BETWEEN = 'BETWEEN';
    public const NOT_BETWEEN = 'NOT_BETWEEN';

    public function __construct()
    {
        $config = [
            'name' => 'DateComparisonOperator',
            'values' => [
                self::BEFORE => [],
                self::AFTER => [],
                self::BETWEEN => [],
                self::NOT_BETWEEN => [],
            ],
        ];

        parent::__construct($config);
    }
}
