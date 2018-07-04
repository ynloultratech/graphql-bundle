<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Types;

use GraphQL\Type\Definition\EnumType;

class PostType extends EnumType
{
    public const ARTICLE = 'ARTICLE';
    public const PAGE = 'PAGE';

    public function __construct()
    {
        $config = [
            'name'=> 'PostType',
            'values' => [
                self::ARTICLE,
                self::PAGE,
            ],
        ];

        parent::__construct($config);
    }
}