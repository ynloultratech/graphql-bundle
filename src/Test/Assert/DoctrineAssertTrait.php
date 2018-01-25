<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\Assert;

use Symfony\Bundle\FrameworkBundle\Client;
use Ynlo\GraphQLBundle\Model\ID;

/**
 * @method Client getClient()
 *
 * @requires DoctrineHelperTrait
 */
trait DoctrineAssertTrait
{
    /**
     * @param string $class
     * @param array  $criteria
     */
    public static function assertRepositoryContains($class, $criteria)
    {
        static::assertNotNull(static::getRepository($class)->findOneBy($criteria));
    }

    /**
     * @param string $class
     * @param array  $criteria
     */
    public static function assertRepositoryNotContains($class, $criteria)
    {
        static::assertNull(static::getRepository($class)->findOneBy($criteria));
    }

    /**
     * @param string $class
     * @param mixed  $id
     *
     * @return mixed
     */
    public static function findOneById($class, $id)
    {
        $databaseId = ID::createFromString($id)->getDatabaseId();

        return static::getRepository($class)->find($databaseId);
    }
}
