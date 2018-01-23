<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Ynlo\GraphQLBundle\Model\ID;

/**
 * @method Client getClient()
 */
trait DoctrineORMHelperTrait
{
    /**
     * @return Registry
     */
    public static function getDoctrine(): Registry
    {
        return static::getClient()->getKernel()->getContainer()->get('doctrine');
    }

    /**
     * @param string $class
     *
     * @return ObjectRepository|EntityRepository
     */
    public static function getRepository($class): ObjectRepository
    {
        return static::getDoctrine()->getRepository($class);
    }

    /**
     * @param string $class
     * @param array  $criteria
     */
    public static function assertRepositoryContains($class, $criteria)
    {
        static::assertNotNull(self::getRepository($class)->findOneBy($criteria));
    }

    /**
     * @param string $class
     * @param array  $criteria
     */
    public static function assertRepositoryNotContains($class, $criteria)
    {
        static::assertNull(self::getRepository($class)->findOneBy($criteria));
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
