<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Exception;

use GraphQL\Error\ClientAware;

interface ControlledErrorInterface extends \Throwable, ClientAware
{
    /**
     * The description will be only used for documentation purposes
     * and is not available in the response.
     *
     * @return string
     */
    public function getDescription(): ?string;
}
