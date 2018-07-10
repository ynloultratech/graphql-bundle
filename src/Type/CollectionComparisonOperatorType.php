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

class CollectionComparisonOperatorType extends EnumType
{
    public const IN = 'IN';
    public const NIN = 'NIN';

    public function __construct()
    {
        $config = [
            'name' => 'CollectionComparisonOperator',
            'values' => [
                self::IN => ['description' => 'Elements In'],
                self::NIN => ['description' => 'Elements Not In'],
            ],
        ];

        parent::__construct($config);
    }
}
