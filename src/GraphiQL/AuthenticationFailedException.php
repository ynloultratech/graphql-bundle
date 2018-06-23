<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\GraphiQL;

use Throwable;

/**
 * AuthenticationFailedException
 */
class AuthenticationFailedException extends \Exception
{
    /**
     * @inheritDoc
     */
    public function __construct(string $message = 'Authentication Failed', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
