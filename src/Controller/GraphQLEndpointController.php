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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Ynlo\GraphQLBundle\Schema\SchemaCompiler;

class GraphQLEndpointController
{
    private $compiler;
    private $debug;
    private $logger;

    public function __construct(SchemaCompiler $compiler, bool $debug, LoggerInterface $logger = null)
    {
        $this->compiler = $compiler;
        $this->debug = $debug;
        $this->logger = $logger;
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->debug && $request->getMethod() !== Request::METHOD_POST) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The method should be POST to talk with GraphQL API');
        }

        $schema = $this->compiler->compile();

        $input = json_decode($request->getContent(), true);
        $query = $input['query'];
        $variableValues = $input['variables'] ?? null;
        $operationName = $input['operationName'] ?? null;

        try {
            $schema->assertValid();
            $context = null;
            $result = GraphQL::executeQuery($schema, $query, $context, null, $variableValues, $operationName);

            $debug = false;
            if ($this->debug) {
                $debug = Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE;
            }

            $output = $result->toArray($debug);
            $statusCode = Response::HTTP_OK;

            if (isset($output['errors'])) {
                $statusCode = Response::HTTP_BAD_REQUEST;
            }
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->error($e->getMessage(), $e->getTrace());
            }
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $output['errors']['message'] = $e->getMessage();
            $output['errors']['category'] = 'internal';

            if ($this->debug) {
                $output['errors']['trace'] = $e->getTraceAsString();
            }
        }

        return JsonResponse::create($output, $statusCode);
    }
}
