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
     * @Given /^grab "(.*?)" to use as "(.*?)"$/
     */
    public function grabToUseAs($value, $name)
    {
        $this->storage->setValue($name, $value);
    }

    /**
     * Print helpful debug information for settled variables
     *
     * @Then debug grabbed variables
     */
    public function debugGrabbedVariables()
    {
        print_r("\n\n");
        print_r("\033[46m------------------- VARIABLES-----------------------\033[0m\n\n");
        $variables = $this->storage->getData() ?? null;
        print_r(json_encode($variables, JSON_PRETTY_PRINT));
        print_r("\n\n");
        print_r("-----------------------------------------------------------------\n\n");
        ob_flush();
    }
}
