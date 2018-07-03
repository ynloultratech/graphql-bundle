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

class NumberComparisonOperatorType extends EnumType
{
    public const EQ = 'EQ';
    public const NEQ = 'NEQ';
    public const LT = 'LT';
    public const LTE = 'LTE';
    public const GT = 'GT';
    public const GTE = 'GTE';
    public const BETWEEN = 'BETWEEN';

    public function __construct()
    {
        $config = [
            'name' => 'NumberComparisonOperator',
            'values' => [
                self::EQ => ['description' => 'Equals'],
                self::NEQ => ['description' => 'Not Equals'],
                self::LT => ['description' => 'Lower Than'],
                self::LTE => ['description' => 'Lower Than or Equals'],
                self::GT => ['description' => 'Greater Than'],
                self::GTE => ['description' => 'Greater Than or Equals'],
                self::BETWEEN => ['description' => 'Between values *(Given values are included)*'],
            ],
        ];

        parent::__construct($config);
    }
}
