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
use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Util\IDEncoder;

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
     * @return NodeInterface
     */
    public static function findOneByGlobalId($id)
    {
        return IDEncoder::decode($id);
    }
}
