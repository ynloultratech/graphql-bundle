<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\FixtureLoader\DataPopulator;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Ynlo\GraphQLBundle\Test\FixtureLoader\Cache\CachedFixtureExecutor;
use Ynlo\GraphQLBundle\Test\FixtureLoader\Cache\CachedFixtureEntityManager;

/**
 * Class CachedORMDataLoader
 */
class CachedORMDataLoader implements DataLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Registry $registry): bool
    {
        return $registry->getName() === 'ORM';
    }

    /**
     * {@inheritdoc}
     */
    public function createExecutor(Registry $registry): AbstractExecutor
    {
        /** @var EntityManagerInterface $em */
        $em = $registry->getManager();
        $om = new CachedFixtureEntityManager($em);
        $purger = new ORMPurger();

        return new CachedFixtureExecutor($om, $purger);
    }
}
