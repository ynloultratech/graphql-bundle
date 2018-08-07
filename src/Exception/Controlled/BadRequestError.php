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
use Ynlo\GraphQLBundle\Exception\ControlledError;

class BadRequestError extends ControlledError
{
    protected $code = 400;

    protected $message = 'Bad Request';

    protected $description = 'The request was invalid or cannot be otherwise served.';

    /**
     * {@inheritDoc}
     */
    public function getCategory()
    {
        return Error::CATEGORY_GRAPHQL;
    }
}
