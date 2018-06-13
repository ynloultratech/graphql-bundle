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

use GraphQL\Error\Error;
use Ynlo\GraphQLBundle\Exception\AbstractControlledError;

class InternalServerError extends AbstractControlledError
{
    protected $code = 500;

    protected $message = 'Internal Server Error';

    protected $description = 'Something is broken. This is usually a temporary error, for example in a high load situation or if an endpoint is temporarily having issues.';

    /**
     * {@inheritDoc}
     */
    public function getCategory()
    {
        return Error::CATEGORY_INTERNAL;
    }
}
