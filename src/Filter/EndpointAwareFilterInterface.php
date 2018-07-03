<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter;

/**
 * If a filter can be displayed only for certain endpoints
 * should implement this interface.
 */
interface EndpointAwareFilterInterface
{
    /**
     * Endpoints to display this filter,
     * return empty array to display in all endpoints.
     *
     * @return array
     */
    public function getEndpoints(): array;
}
