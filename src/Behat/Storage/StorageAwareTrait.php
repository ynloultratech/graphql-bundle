<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Storage;

trait StorageAwareTrait
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @param Storage $storage
     *
     * @return StorageAwareInterface
     */
    public function setStorage(Storage $storage): StorageAwareInterface
    {
        $this->storage = $storage;

        return $this;
    }
}
