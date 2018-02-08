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

use GraphQL\Error\ClientAware;
use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules;
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

        $input = json_decode($request->getContent(), true);
        $query = $input['query'];
        $context = null;
        $variableValues = $input['variables'] ?? null;
        $operationName = $input['operationName'] ?? null;
        // this will override global validation rules for this request
        $validationRules = null;

        try {
            $schema = $this->compiler->compile();
            $schema->assertValid();

            $result = GraphQL::executeQuery($schema, $query, null, $context, $variableValues, $operationName, null, $validationRules);

            if (!$this->debug) {
                // in case of debug = false
                // If API_DEBUG is passed, output of error formatter is enriched which debugging information.
                // Helpful for tests to get full error logs without the need of enable full app debug flag
                if (isset($_ENV['API_DEBUG'])) {
                    $this->debug = $_ENV['API_DEBUG'];
                } elseif (isset($_SERVER['API_DEBUG'])) {
                    $this->debug = $_SERVER['API_DEBUG'];
                }
            }

            $debugFlags = false;
            if ($this->debug) {
                $debugFlags = Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE;
            }

            $output = $result->toArray($debugFlags);
            $statusCode = Response::HTTP_OK;
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->error($e->getMessage(), $e->getTrace());
            }
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = Response::$statusTexts[$statusCode] ?? 'Internal Server Error';

            if ($this->debug || ($e instanceof ClientAware && $e->isClientSafe())) {
                $message = $e->getMessage();
            }

            $output['errors']['message'] = $message;
            $output['errors']['category'] = 'internal';

            if ($this->debug) {
                $output['errors']['trace'] = $e->getTraceAsString();
            }
        }

        return JsonResponse::create($output, $statusCode);
    }

    public function addGlobalValidationRules(array $validationRules): void
    {
        $rules = [];
        if (!empty($validationRules['query_complexity'])) {
            $rules[] = new Rules\QueryComplexity($validationRules['query_complexity']);
        }
        if (!empty($validationRules['query_depth'])) {
            $rules[] = new Rules\QueryDepth($validationRules['query_depth']);
        }
        if (!empty($validationRules['disable_introspection'])) {
            $rules[] = new Rules\DisableIntrospection();
        }
        array_map([DocumentValidator::class, 'addRule'], $rules);
    }
}
