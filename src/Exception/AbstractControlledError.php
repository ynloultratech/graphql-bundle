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

use GraphQL\Error\UserError;

abstract class AbstractControlledError extends UserError implements ControlledErrorInterface
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
