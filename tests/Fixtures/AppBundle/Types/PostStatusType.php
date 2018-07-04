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

class PostStatusType extends EnumType
{
    public const DRAFT = 'DRAFT';
    public const PUBLISH = 'PUBLISH';
    public const TRASH = 'TRASH';

    public function __construct()
    {
        $config = [
            'values' => [
                self::DRAFT,
                self::PUBLISH,
                self::TRASH,
            ],
        ];

        parent::__construct($config);
    }
}