<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Type;

use GraphQL\Type\Definition\EnumType;

class PostStatusType extends EnumType
{
    public const DRAFT = 'DRAFT';
    public const PENDING = 'PENDING';
    public const PUBLISH = 'PUBLISH';

    /**
     * PostStatusType constructor.
     */
    public function __construct()
    {
        $config = [
            'name' => 'PostStatus',
            'values' => [
                self::DRAFT => [
                    'description' => 'The post is in draft',
                ],
                self::PENDING => [
                    'description' => 'The post is ready to publish pending review',
                ],
                self::PUBLISH => [
                    'description' => 'The post has been published',
                ],
            ],
        ];

        parent::__construct($config);
    }
}
