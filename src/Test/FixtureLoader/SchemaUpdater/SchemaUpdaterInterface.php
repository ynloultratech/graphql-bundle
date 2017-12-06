<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\FixtureLoader\SchemaUpdater;

use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Interface SchemaUpdaterInterface
 */
interface SchemaUpdaterInterface
{
    /**
     * @param Registry $registry
     *
     * @return mixed
     */
    public function supports(Registry $registry);

    /**
     * @param Registry $registry
     *
     * @return mixed
     */
    public function updateSchema(Registry $registry);
}
