<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\DBAL\Types;

use Ynlo\GraphQLBundle\Doctrine\DBAL\Types\AbstractEnumType;

/**
 * PostStatusType
 */
class PostStatusType extends AbstractEnumType
{
    public const PUBLISH = 'PUBLISH';
    public const DRAFT = 'DRAFT';
    public const PENDING = 'PENDING';
    public const TRASH = 'TRASH';

    /** @deprecated */
    public const DELETED = 'DELETED';

    protected static $choices = [
        self::PUBLISH => 'Publish',
        self::DRAFT => 'Draft',
        self::PENDING => 'Pending',
        self::TRASH => 'Trash',
        self::DELETED => 'Deleted',
    ];

    protected static $descriptions = [
        self::PUBLISH => 'Viewable by everyone.',
        self::DRAFT => 'Incomplete post viewable by anyone with proper user role.',
        self::PENDING => 'Awaiting a user with permissions tu publish.',
        self::TRASH => 'Posts in the Trash are assigned the trash status.',
        self::DELETED => 'The post has been marked as deleted',
    ];

    protected static $deprecatedReasons = [
        self::DELETED => 'This status is useless, 
        deleted post has been removed from database permanently. 
        For temporal removal use TRASH instead.',
    ];

    protected static $publicNames = [
        self::PUBLISH => 'PUBLISHED',
    ];
}
