<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type\Definition;

use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Interface EndpointAwareInterface
 */
interface EndpointAwareInterface
{
    /**
     * @param Endpoint $endpoint
     *
     * @return mixed
     */
    public function setEndpoint(Endpoint $endpoint);
}
