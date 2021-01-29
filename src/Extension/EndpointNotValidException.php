<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Extension;

class EndpointNotValidException extends \RuntimeException
{
    /**
     * @inheritDoc
     */
    public function __construct(string $endpoint, array $registeredEndpoints)
    {
        $message = sprintf(
            '"%s" is not a valid configured endpoint, use one of the following endpoints: [%s]',
            $endpoint,
            implode(',', $registeredEndpoints)
        );
        parent::__construct($message);
    }
}