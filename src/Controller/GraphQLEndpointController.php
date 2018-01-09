<?php
/*introspection*/

namespace Ynlo\GraphQLBundle\Controller;

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
        $validationRules = $this->getValidationRules($query);

        try {
            $schema = $this->compiler->compile();
            $schema->assertValid();

            $result = GraphQL::executeQuery($schema, $query, null, $context, $variableValues, $operationName, null, $validationRules);

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

    private function getValidationRules(string $query): ?array
    {
        $rules = [];

        // disable query complexity limit for introspection query
        if (false !== strpos($query, "query IntrospectionQuery {\n    __schema {") && null !== DocumentValidator::getRule('QueryComplexity')) {
            $rules[] = new Rules\QueryComplexity(Rules\QueryComplexity::DISABLED);
        }

        if (!$rules) {
            return null;
        }

        return array_merge(GraphQL::getStandardValidationRules(), $rules);
    }
}
