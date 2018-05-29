<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * This middleware interface allow parse and prepare the query to execute
 * using the current given request.
 *
 * Implementing custom middleware can use your custom logic to process custom requests
 */
interface RequestMiddlewareInterface
{
    /**
     * Process the given request and alter the given query based on that
     *
     * @param Request      $request
     * @param ExecuteQuery $query
     *
     * @return void
     */
    public function processRequest(Request $request, ExecuteQuery $query): void;
}
