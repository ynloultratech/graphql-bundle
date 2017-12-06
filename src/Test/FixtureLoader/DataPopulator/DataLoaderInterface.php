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

/**
 * Interface DataLoaderInterface
 */
interface DataLoaderInterface
{
    /**
     * @param Registry $registry
     *
     * @return bool
     */
    public function supports(Registry $registry): bool;

    /**
     * @param Registry $registry
     *
     * @return AbstractExecutor
     */
    public function createExecutor(Registry $registry): AbstractExecutor;
}
