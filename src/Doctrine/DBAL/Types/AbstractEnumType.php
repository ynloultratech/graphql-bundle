<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Doctrine\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType as BaseEnumType;

/**
 * EnumType
 */
class AbstractEnumType extends BaseEnumType
{
    /**
     * Set deprecations reasons for deprecated values
     *
     * @var array
     */
    protected static $deprecatedReasons = [];

    /**
     * Set description for each values
     *
     * @var array
     */
    protected static $descriptions = [];

    /**
     * If the public name should
     * be different to the internal one
     *
     * @var array
     */
    protected static $publicNames = [];

    /**
     * @param string $type
     *
     * @return null|string
     */
    public static function getDeprecatedReason($type): ?string
    {
        return static::$deprecatedReasons[$type] ?? null;
    }

    /**
     * @param string $type
     *
     * @return null|string
     */
    public static function getDescription($type): ?string
    {
        return static::$descriptions[$type] ?? null;
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public static function getPublicName($type)
    {
        return static::$publicNames[$type] ?? null;
    }
}
