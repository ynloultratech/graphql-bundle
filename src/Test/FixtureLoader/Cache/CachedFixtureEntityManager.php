<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\FixtureLoader\Cache;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * This is a proxy entity manager to save in cache all
 * fixtures without do a real persist or flush
 * @see CachedFixtureExecutor
 */
class CachedFixtureEntityManager implements EntityManagerInterface
{
    protected $cachedForPersist = [];

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getCachedForPersist(): array
    {
        return $this->cachedForPersist;
    }

    /**
     * {@inheritDoc}
     */
    public function find($className, $id)
    {
        return $this->em->find($className, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function persist($object)
    {
        $oid = spl_object_hash($object);
        $this->cachedForPersist[$oid] = $object;
    }

    /**
     * {@inheritDoc}
     */
    public function persistReal($object)
    {
        $this->em->persist($object);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($object)
    {
        $this->em->remove($object);
    }

    /**
     * {@inheritDoc}
     */
    public function merge($object)
    {
        return $this->em->merge($object);
    }

    /**
     * {@inheritDoc}
     */
    public function clear($objectName = null)
    {
        $this->em->clear($objectName);
    }

    /**
     * {@inheritDoc}
     */
    public function detach($object)
    {
        $this->em->detach($object);
    }

    /**
     * {@inheritDoc}
     */
    public function refresh($object)
    {
        $this->em->refresh($object);
    }

    /**
     * {@inheritDoc}
     */
    public function flush($entity = null)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function flushReal()
    {
        $this->em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository($className)
    {
        return $this->em->getRepository($className);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata($className): ClassMetadata
    {
        return $this->em->getClassMetadata($className);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataFactory()
    {
        return $this->em->getMetadataFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function initializeObject($obj)
    {
        $this->em->initializeObject($obj);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($object)
    {
        return $this->em->contains($object);
    }

    /**
     *{@inheritDoc}
     */
    public function getCache()
    {
        return $this->em->getCache();
    }

    /**
     *{@inheritDoc}
     */
    public function getConnection()
    {
        return $this->em->getConnection();
    }

    /**
     *{@inheritDoc}
     */
    public function getExpressionBuilder()
    {
        return $this->em->getExpressionBuilder();
    }

    /**
     *{@inheritDoc}
     */
    public function beginTransaction()
    {
        $this->em->beginTransaction();
    }

    /**
     *{@inheritDoc}
     */
    public function transactional($func)
    {
        $this->cachedForPersist = [];
        $this->em->transactional($func);
    }

    /**
     *{@inheritDoc}
     */
    public function commit()
    {
        $this->em->commit();
    }

    /**
     *{@inheritDoc}
     */
    public function rollback()
    {
        $this->em->rollback();
    }

    /**
     *{@inheritDoc}
     */
    public function createQuery($dql = '')
    {
        return $this->em->createQuery($dql);
    }

    /**
     *{@inheritDoc}
     */
    public function createNamedQuery($name)
    {
        return $this->em->createNamedQuery($name);
    }

    /**
     *{@inheritDoc}
     */
    public function createNativeQuery($sql, ResultSetMapping $rsm)
    {
        return $this->em->createNativeQuery($sql, $rsm);
    }

    /**
     *{@inheritDoc}
     */
    public function createNamedNativeQuery($name)
    {
        return $this->em->createNamedNativeQuery($name);
    }

    /**
     *{@inheritDoc}
     */
    public function createQueryBuilder()
    {
        return $this->em->createQueryBuilder();
    }

    /**
     *{@inheritDoc}
     */
    public function getReference($entityName, $id)
    {
        return $this->em->getReference($entityName, $id);
    }

    /**
     *{@inheritDoc}
     */
    public function getPartialReference($entityName, $identifier)
    {
        return $this->em->getPartialReference($entityName, $identifier);
    }

    /**
     *{@inheritDoc}
     */
    public function close()
    {
        $this->em->close();
    }

    /**
     *{@inheritDoc}
     */
    public function copy($entity, $deep = false)
    {
        return $this->em->copy($entity, $deep);
    }

    /**
     *{@inheritDoc}
     */
    public function lock($entity, $lockMode, $lockVersion = null)
    {
        $this->em->lock($entity, $lockMode, $lockVersion);
    }

    /**
     *{@inheritDoc}
     */
    public function getEventManager()
    {
        return $this->em->getEventManager();
    }

    /**
     *{@inheritDoc}
     */
    public function getConfiguration()
    {
        return $this->em->getConfiguration();
    }

    /**
     *{@inheritDoc}
     */
    public function isOpen()
    {
        return $this->em->isOpen();
    }

    /**
     *{@inheritDoc}
     */
    public function getUnitOfWork()
    {
        return $this->em->getUnitOfWork();
    }

    /**
     *{@inheritDoc}
     */
    public function getHydrator($hydrationMode)
    {
        return $this->em->getHydrator($hydrationMode);
    }

    /**
     *{@inheritDoc}
     */
    public function newHydrator($hydrationMode)
    {
        return $this->em->newHydrator($hydrationMode);
    }

    /**
     *{@inheritDoc}
     */
    public function getProxyFactory()
    {
        return $this->em->getProxyFactory();
    }

    /**
     *{@inheritDoc}
     */
    public function getFilters()
    {
        return $this->em->getFilters();
    }

    /**
     *{@inheritDoc}
     */
    public function isFiltersStateClean()
    {
        return $this->em->isFiltersStateClean();
    }

    /**
     *{@inheritDoc}
     */
    public function hasFilters()
    {
        return $this->em->hasFilters();
    }
}
