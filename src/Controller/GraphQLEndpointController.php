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
use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Ynlo\GraphQLBundle\Error\ErrorFormatterInterface;
use Ynlo\GraphQLBundle\Error\ErrorHandlerInterface;
use Ynlo\GraphQLBundle\Error\ErrorQueue;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLOperationEvent;
use Ynlo\GraphQLBundle\Request\ExecuteQuery;
use Ynlo\GraphQLBundle\Request\RequestMiddlewareInterface;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;
use Ynlo\GraphQLBundle\Schema\SchemaCompiler;
use Ynlo\GraphQLBundle\Security\EndpointResolver;
use Ynlo\GraphQLBundle\Subscription\SubscriptionRequest;

class GraphQLEndpointController
{
    /**
     * @var EndpointResolver
     */
    protected $resolver;

    /**
     * @var SchemaCompiler
     */
    protected $compiler;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * App Config
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var ErrorFormatterInterface
     */
    protected $errorFormatter;

    /**
     * @var ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var iterable
     */
    protected $middlewares = [];

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * GraphQLEndpointController constructor.
     *
     * @param EndpointResolver $endpointResolver
     * @param SchemaCompiler   $compiler
     */
    public function __construct(EndpointResolver $endpointResolver, SchemaCompiler $compiler)
    {
        $this->resolver = $endpointResolver;
        $this->compiler = $compiler;
    }

    /**
     * @param ErrorFormatterInterface $errorFormatter
     */
    public function setErrorFormatter(ErrorFormatterInterface $errorFormatter): void
    {
        $this->errorFormatter = $errorFormatter;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param ErrorHandlerInterface $errorHandler
     */
    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @param LoggerInterface|null $logger
     */
    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @param iterable $middlewares
     */
    public function setMiddlewares(iterable $middlewares): void
    {
        $this->middlewares = $middlewares;
    }

    /**
     * @param PublisherInterface $publisher
     */
    public function setPublisher(PublisherInterface $publisher): void
    {
        $this->publisher = $publisher;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $operationEvent = null;

        if (!$this->debug && $request->getMethod() !== Request::METHOD_POST) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The method should be POST to talk with GraphQL API');
        }

        try {
            $query = new ExecuteQuery();
            foreach ($this->middlewares as $middleware) {
                if ($middleware instanceof RequestMiddlewareInterface) {
                    $middleware->processRequest($request, $query);
                }
            }

            $endpoint = $this->resolver->resolveEndpoint($request);
            if (!$endpoint) {
                throw new AccessDeniedHttpException();
            }

            if ($this->dispatcher) {
                $operationEvent = new GraphQLOperationEvent($query, $endpoint);
                $this->dispatcher->dispatch(GraphQLEvents::OPERATION_START, $operationEvent);
            }

            $context = new ResolverContext($endpoint);
            $validationRules = null;

            $schema = $this->compiler->compile($endpoint);
            $schema->assertValid();

            if ($subscriptionRequest = $query->getSubscriptionRequest()) {
                $context->setMeta('subscriptionRequest', $subscriptionRequest);
            }

            $result = GraphQL::executeQuery(
                $schema,
                $query->getRequestString(),
                null,
                $context,
                $query->getVariables(),
                $query->getOperationName(),
                null,
                $validationRules
            );

            //https://webonyx.github.io/graphql-php/error-handling/
            $formatter = $this->errorFormatter;
            $handler = $this->errorHandler;

            //get queued errors
            $exceptions = ErrorQueue::all();
            foreach ($exceptions as $exception) {
                $result->errors[] = Error::createLocatedError($exception);
            }

            $result->setErrorFormatter([$formatter, 'format']);
            $result->setErrorsHandler(
                function ($errors) use ($handler, $formatter) {
                    return $handler->handle($errors, $formatter, $this->getDebugMode());
                }
            );

            $output = $result->toArray($this->getDebugMode());
            $statusCode = Response::HTTP_OK;
        } catch (\Exception $e) {
            $error = Error::createLocatedError($e);
            $errors = $this->errorHandler->handle([$error], $this->errorFormatter, $this->debug);
            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();
            } elseif ($e instanceof ClientAware) {
                // usually client's exceptions do not arrive until here
                // but sometimes this exception happen during compilation time, like: Ynlo\GraphQLBundle\Type\Registry\InvalidTypeException
                // due to invalid user request
                $statusCode = Response::HTTP_BAD_REQUEST;
            } else {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            $output = [
                'errors' => $errors,
            ];
        }

        if ($this->dispatcher && $operationEvent) {
            $this->dispatcher->dispatch(GraphQLEvents::OPERATION_END, $operationEvent);
        }

        if ($this->publisher && isset($subscriptionRequest) && $subscriptionRequest instanceof SubscriptionRequest) {
            ($this->publisher)(new Update($subscriptionRequest->getId(), json_encode($output)));
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

    /**
     * @return bool|int
     */
    private function getDebugMode()
    {
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
            if ($this->config['error_handling']['show_trace'] ?? true) {
                $debugFlags = Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE;
            } else {
                $debugFlags = Debug::INCLUDE_DEBUG_MESSAGE;
            }
        }

        return $debugFlags;
    }
}
