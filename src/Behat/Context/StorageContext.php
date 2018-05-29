<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Ynlo\GraphQLBundle\Behat\Storage\StorageAwareInterface;
use Ynlo\GraphQLBundle\Behat\Storage\StorageAwareTrait;

/**
 * Work with the storage to save values temporarily to re-use during steps
 */
final class StorageContext implements Context, StorageAwareInterface
{
    use StorageAwareTrait;

    /**
     * Store a value to re-use later
     *
     * Example:
     *
     * <code>
     *  - And grab "{response.data.add.order.items}" to use as "orderItems"
     * </code>
     *
     * @Given /^grab "([^"]*)" to use as "([^"]*)"$/
     */
    public function grabToUseAs($value, $name)
    {
        $this->storage->setValue($name, $value);
    }
}
