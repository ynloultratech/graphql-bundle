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

/**
 * OrderDirectionType
 */
class OrderDirectionType extends EnumType
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    /**
     * PostStatusType constructor.
     */
    public function __construct()
    {
        $config = [
            'name' => 'OrderDirection',
            'values' => [
                self::ASC => [
                    'description' => 'Ascending Order',
                ],
                self::DESC => [
                    'description' => 'Descending Order',
                ],
            ],
        ];

        parent::__construct($config);
    }
}
