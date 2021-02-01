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

namespace Ynlo\GraphQLBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * HttpKernel aware interface for contexts.
 */
interface KernelAwareContext extends Context
{
    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel);
}