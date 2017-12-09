<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Error;

use GraphQL\Error\UserError;
use Throwable;

/**
 * Class NodeNotFoundException
 */
class NodeNotFoundException extends UserError
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = null, $code = 0, Throwable $previous = null)
    {
        $message = $message ?? 'Node not Found';
        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getCategory()
    {
        return 'NOT_FOUND';
    }
}
