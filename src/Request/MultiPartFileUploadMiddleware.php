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
 * Middleware to support "GraphQL multipart request specification"
 *
 * @see https://github.com/jaydenseric/graphql-multipart-request-spec
 */
class MultiPartFileUploadMiddleware implements RequestMiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function processRequest(Request $request, ExecuteQuery $query): void
    {
        //TODO: add support for batching
        if (mb_stripos($request->headers->get('Content-Type'), 'multipart/form-data') !== false) {
            if (($operations = $request->get('operations')) && ($map = $request->get('map'))) {
                $result = json_decode($operations, true);
                $map = json_decode($map, true);
                foreach ($map as $fileKey => $locations) {
                    foreach ($locations as $location) {
                        $items = &$result;
                        foreach (explode('.', $location) as $key) {
                            if (!isset($items[$key]) || !is_array($items[$key])) {
                                $items[$key] = [];
                            }
                            $items = &$items[$key];
                        }

                        $items = $request->files->get($fileKey);
                    }
                }

                $query->setRequestString($result['query'] ?? null);
                $query->setVariables($result['variables'] ?? []);
                $query->setOperationName($result['operationName'] ?? null);
            }
        }
    }
}
