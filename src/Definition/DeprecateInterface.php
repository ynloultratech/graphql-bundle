<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition;

/**
 * Interface DeprecateInterface
 */
interface DeprecateInterface
{

    /**
     * @param string $deprecationReason
     *
     * @return DeprecateInterface
     */
    public function setDeprecationReason(?string $deprecationReason): DeprecateInterface;

    /**
     * @return string
     */
    public function getDeprecationReason():?string;

    /**
     * @return bool
     */
    public function isDeprecated(): bool;
}
