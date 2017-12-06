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
        return self::getClient()->getKernel()->getContainer()->get('doctrine');
    }

    /**
     * @param string $class
     *
     * @return ObjectRepository
     */
    public static function getRepository($class): ObjectRepository
    {
        return self::getDoctrine()->getRepository($class);
    }

    /**
     * @param string $class
     * @param array  $criteria
     */
    public static function assertRepositoryContains($class, $criteria)
    {
        self::assertNotNull(self::getRepository($class)->findOneBy($criteria));
    }

    /**
     * @param string $class
     * @param array  $criteria
     */
    public static function assertRepositoryNotContains($class, $criteria)
    {
        self::assertNull(self::getRepository($class)->findOneBy($criteria));
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

        return self::getRepository($class)->find($databaseId);
    }
}
