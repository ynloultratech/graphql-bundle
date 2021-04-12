<?php
/*
 * ******************************************************************************
 * This file is part of the GraphQL Bundle package.
 *
 * (c) YnloUltratech <support@ynloultratech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  *****************************************************************************
 */

namespace Ynlo\GraphQLBundle\Elastic;

use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;

class ElasticRepositoryManager
{
    protected RepositoryManagerInterface $manager;

    public function __construct(RepositoryManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return RepositoryManagerInterface
     */
    public function getManager(): RepositoryManagerInterface
    {
        return $this->manager;
    }
}