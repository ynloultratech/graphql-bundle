<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Client;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Behat\Deprecation\DeprecationAdviser;

/**
 * Client to communicate between behat tests and symfony kernel
 * using GraphQL Operations.
 *
 * Bring access to the symfony kernel in order to get services etc.
 */
class GraphQLClient extends KernelBrowser
{
    /**
     * @var string
     */
    protected $endpoint = '';

    /**
     * @var array
     */
    protected $serverParameters = [];

    /**
     * @var array
     */
    protected $requestsParameters = [];

    /**
     * @var string|null
     */
    protected $graphQL;

    /**
     * @var string|null
     */
    protected $operationName;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var Response|null
     */
    protected $response;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var DeprecationAdviser
     */
    protected $deprecationAdviser = [];

    public function __construct(KernelInterface $kernel, DeprecationAdviser $deprecationAdviser = null, $config = [])
    {
        parent::__construct($kernel);

        $this->config = $config;
        $this->deprecationAdviser = $deprecationAdviser;
    }

    public function restart()
    {
        parent::restart();

        $this->endpoint = '';
        $this->graphQL = null;
        $this->operationName = null;
        $this->variables = [];
        $this->response = null;
        $this->server = [];
        $this->requestsParameters = [];
    }

    /**
     * @return null|string
     */
    public function getGraphQL(): ?string
    {
        return $this->graphQL;
    }

    /**
     * @param null|string $graphQL
     *
     * @return GraphQLClient
     */
    public function setGraphQL(?string $graphQL): GraphQLClient
    {
        $this->graphQL = $graphQL;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     *
     * @return GraphQLClient
     */
    public function setEndpoint(string $endpoint): GraphQLClient
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return array
     */
    public function getServerParameters(): array
    {
        return $this->server;
    }

    /**
     * @return array
     */
    public function getRequestsParameters(): array
    {
        return $this->requestsParameters;
    }

    /**
     * @param array $requestsParameters
     *
     * @return GraphQLClient
     */
    public function setRequestsParameters(array $requestsParameters): GraphQLClient
    {
        $this->requestsParameters = $requestsParameters;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getOperationName(): ?string
    {
        return $this->operationName;
    }

    /**
     * @param null|string $operationName
     *
     * @return GraphQLClient
     */
    public function setOperationName(?string $operationName): GraphQLClient
    {
        $this->operationName = $operationName;

        return $this;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     *
     * @return GraphQLClient
     */
    public function setVariables(array $variables): GraphQLClient
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Send the configured query or mutation with given variables
     *
     * @param bool|null $insulated
     *
     * @return Response
     */
    public function sendQuery($insulated = null): Response
    {
        $data = [
            'query' => $this->getGraphQL(),
            'variables' => $this->getVariables(),
        ];
        if ($this->operationName) {
            $data['operationName'] = $this->operationName;
        }

        $content = json_encode($data);
        $this->insulated = $insulated ?? ($this->config['insulated'] ?? false);

        $this->sendRequest(Request::METHOD_POST, $this->getEndpoint(), $this->getRequestsParameters(), [], $this->getServerParameters(), $content);

        return $this->response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * This method works like `request` in the parent class, but has a error handler to catch all deprecation notices.
     * Can`t be named `request` to override the parent because in projects using symfony4 the signature for this method has been changed
     * using strict types on each argument.
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $files
     * @param array  $server
     * @param null   $content
     * @param bool   $changeHistory
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function sendRequest($method, $uri, array $parameters = [], array $files = [], array $server = [], $content = null, $changeHistory = true)
    {
        set_error_handler(
            function ($level, $message, $errFile, $errLine) {
                if ($this->deprecationAdviser) {
                    $this->deprecationAdviser->addWarning($message, $errFile, $errLine);
                }
            },
            E_USER_DEPRECATED
        );

        $result = parent::request($method, $uri, $parameters, $files, $server, $content, $changeHistory);

        restore_error_handler();

        return $result;
    }
}
