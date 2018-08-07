<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type\Registry;

use GraphQL\Error\ClientAware;

/**
 * This exception happen when some type definition does not exist in current endpoint.
 * Commonly happen for invalid schema in the server side or if the client are trying to send a request not
 * existent in current endpoint. In any case should be treated as `graphql` error and client safe
 */
class InvalidTypeException extends \UnexpectedValueException implements ClientAware
{
    /**
     * InvalidTypeException constructor.
     *
     * @param string $type
     */
    public function __construct($type)
    {
        parent::__construct(sprintf('Can`t find a valid type for "%s"', $type));
    }

    /**
     * @inheritDoc
     */
    public function isClientSafe()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCategory()
    {
        return 'graphql';
    }
}
