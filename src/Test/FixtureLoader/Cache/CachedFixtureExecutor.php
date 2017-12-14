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

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use function DeepCopy\deep_copy;

/**
 * This executor work with a cache of fixtures to load fixtures only once
 * Once a fixture has been loaded, next executions only load the cached data and populated the database
 *
 * Helpful for fixtures using random data or faker to keep the same data during all tests and avoid
 * entity manager error with cache
 *
 * @see CachedFixtureEntityManager
 * @see CachedReferenceRepository
 */
class CachedFixtureExecutor extends ORMExecutor
{
    protected static $loadedFixtures = [];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager, FixtureInterface $fixture)
    {
        $fixtureClass = get_class($fixture);

        if ($manager instanceof CachedFixtureEntityManager) {
            if (isset(self::$loadedFixtures[$fixtureClass])) {
                $objects = deep_copy(self::$loadedFixtures[$fixtureClass]);
                foreach ($objects as $oid => $object) {
                    $manager->persistReal($object);
                    if ($this->referenceRepository instanceof CachedReferenceRepository) {
                        $this->referenceRepository->setReferenceByObjectId($oid, $object);
                    }
                }
            } else {
                parent::load($manager, $fixture);
                self::$loadedFixtures[$fixtureClass] = $manager->getCachedForPersist();
                $this->load($manager, $fixture);
            }
        } else {
            parent::load($manager, $fixture);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $fixtures, $append = false)
    {
        $executor = $this;
        $this->getObjectManager()->transactional(
            function () use ($executor, $fixtures, $append) {
                if ($append === false) {
                    $executor->purge();
                }
                foreach ($fixtures as $fixture) {
                    $executor->load($this->getObjectManager(), $fixture);
                }
            }
        );
    }
}
