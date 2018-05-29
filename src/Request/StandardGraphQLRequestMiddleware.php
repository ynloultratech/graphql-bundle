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
 * Request middleware compatible with "express" server format:
 * @see https://github.com/graphql/express-graphql
 */
class StandardGraphQLRequestMiddleware implements RequestMiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function processRequest(Request $request, ExecuteQuery $query): void
    {
        $content = $request->getContent();
        if ($content) {
            $input = json_decode($content, true);
            if (isset($input['query'])) {
                $query->setRequestString($input['query']);
            }

            if (isset($input['variables'])) {
                $query->setVariables($input['variables']);
            }

            if (isset($input['operationName'])) {
                $query->setOperationName($input['operationName']);
            }
        }
    }
}
