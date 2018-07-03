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

class NodeComparisonOperatorType extends EnumType
{
    public const IN = 'IN';
    public const NIN = 'NIN';

    public function __construct()
    {
        $config = [
            'name' => 'NodeComparisonOperator',
            'values' => [
                self::IN => ['description' => 'Node In'],
                self::NIN => ['description' => 'Node Not In'],
            ],
        ];

        parent::__construct($config);
    }
}
