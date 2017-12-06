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
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 * Class ORMDataLoader
 */
class ORMDataLoader implements DataLoaderInterface
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
        $om = $registry->getManager();
        $purger = new ORMPurger();

        return new ORMExecutor($om, $purger);
    }
}
