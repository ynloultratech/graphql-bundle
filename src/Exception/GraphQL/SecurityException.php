<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Exception\GraphQL;

use GraphQL\Error\UserError;

class SecurityException extends UserError
{
    /**
     * {@inheritDoc}
     */
    public function getCategory()
    {
        return 'security';
    }
}
