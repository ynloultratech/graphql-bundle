<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Controller;

use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ApiRootEndpointController
 */
class ApiRootEndpointController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function rootAction(Request $request): JsonResponse
    {
        $debugMode = $this->container->getParameter('kernel.debug');

        if (!$debugMode && $request->getMethod() !== Request::METHOD_POST) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The method should be POST to talk with GraphQL API');
        }

        $schema = $this->get('graphql.schema_compiler')->compile();

        $input = json_decode($request->getContent(), true);
        $query = $input['query'];
        $variableValues = isset($input['variables']) ? $input['variables'] : null;
        $operationName = isset($input['operationName']) ? $input['operationName'] : null;

        try {
            $schema->assertValid();
            $context = null;
            $result = GraphQL::executeQuery($schema, $query, $context, null, $variableValues, $operationName);

            $debug = false;
            if ($debugMode) {
                $debug = Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE;
            }

            $output = $result->toArray($debug);
            $statusCode = Response::HTTP_OK;

            if (isset($output['errors'])) {
                $statusCode = Response::HTTP_BAD_REQUEST;
            }
        } catch (\Exception $e) {
            if ($this->has('logger')) {
                $this->get('logger')->error($e->getMessage(), $e->getTrace());
            }
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $output['errors']['message'] = $e->getMessage();
            $output['errors']['category'] = 'internal';

            if ($debugMode) {
                $output['errors']['trace'] = $e->getTraceAsString();
            }
        }

        return JsonResponse::create($output, $statusCode);
    }
}
