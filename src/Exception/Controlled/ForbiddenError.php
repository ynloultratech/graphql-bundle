<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Exception\Controlled;

use Ynlo\GraphQLBundle\Exception\AbstractControlledError;

class ForbiddenError extends AbstractControlledError
{
    protected $code = 403;

    protected $message = 'Forbidden';

    protected $description = 'The request is understood, but it has been refused or access is not allowed.';
}
