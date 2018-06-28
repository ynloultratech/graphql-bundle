<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Resolver;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * This buffer is used to solve the N+1 Problem
 *
 * @see https://secure.phabricator.com/book/phabcontrib/article/n_plus_one/
 *
 * Create a array of pending relations to load and create an only one
 * IN(...) query for all entities of the same type
 */
class DeferredBuffer
{
    private static $deferred = [];
    private static $loaded = false;

    protected $registry;

    /**
     * DeferredBuffer constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param NodeInterface $entity
     */
    public function add(NodeInterface $entity): void
    {
        $class = ClassUtils::getClass($entity);
        self::$deferred[$class][$entity->getId()] = $entity->getId();
    }

    /**
     * Return the loaded entity for given not initialized entity
     */
    public function getLoadedEntity(NodeInterface $entity): NodeInterface
    {
        $class = ClassUtils::getClass($entity);
        if (isset(self::$deferred[$class][$entity->getId()]) && self::$deferred[$class][$entity->getId()] instanceof NodeInterface) {
            return self::$deferred[$class][$entity->getId()];
        }

        //fallback
        return $entity;
    }

    /**
     * Load buffer of entities
     */
    public function loadBuffer(): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        foreach (self::$deferred as $class => $ids) {
            /** @var EntityRepository $repo */
            $repo = $this->registry->getRepository($class);
            $qb = $repo->createQueryBuilder('o', 'o.id');
            $entities = $qb->where($qb->expr()->in('o.id', array_values($ids)))
                           ->getQuery()
                           ->getResult();
            foreach ($entities as $entity) {
                if ($entity instanceof NodeInterface) {
                    self::$deferred[$class][$entity->getId()] = $entity;
                }
            }
        }
    }
}
