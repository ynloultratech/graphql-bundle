<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\Helper;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Model\ID;

/**
 * @method Client getClient()
 *
 * @deprecated in favor of Behat tests
 */
trait DoctrineHelperTrait
{
    public static function getDoctrine(): Registry
    {
        return static::getClient()->getKernel()->getContainer()->get('doctrine');
    }

    /**
     * @param string $class
     *
     * @return ObjectRepository|EntityRepository
     */
    public static function getRepository(string $class): ObjectRepository
    {
        return static::getDoctrine()->getRepository($class);
    }

    /**
     * Find a database record from global id
     *
     * findOneByGlobalId('VXNlcjox') => object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public static function findOneByGlobalId($id)
    {
        $id = ID::createFromString($id);
        $databaseId = $id->getDatabaseId();
        $class = static::getClient()
                       ->getContainer()
                       ->get(DefinitionRegistry::class)
                       ->getEndpoint()
                       ->getClassForType($id->getNodeType());

        return static::getRepository($class)->find($databaseId);
    }
}