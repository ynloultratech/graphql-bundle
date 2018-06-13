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

class UnauthorizedError extends AbstractControlledError
{
    protected $code = 401;

    protected $message = 'Unauthorized';

    protected $description = 'Missing or incorrect authentication credentials. This may also returned in other undefined circumstances.';
}
