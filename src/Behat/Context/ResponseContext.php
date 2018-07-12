<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Definition\Call\Then;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareInterface;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareTrait;
use Ynlo\GraphQLBundle\Util\Json;

/**
 * Context to work with latest response
 */
final class ResponseContext implements Context, ClientAwareInterface
{
    use ClientAwareTrait;

    /** @var GraphQLContext */
    private $graphQLContext;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->graphQLContext = $environment->getContext('Ynlo\GraphQLBundle\Behat\Context\GraphQLContext');
    }

    /**
     * Checks, that current response status is equal to specified
     * Example: Then the response status code should be 200
     * Example: And the response status code should be 400
     *
     * @Then /^the response status code should be (?P<code>\d+)$/
     */
    public function assertResponseStatus($code)
    {
        Assert::assertEquals($this->client->getResponse()->getStatusCode(), $code);
    }

    /**
     * Assert that latest response is a GraphQL error with the given message
     *
     * @Then /^the response is GraphQL error with "([^"]*)"$/
     */
    public function theResponseIsGraphQLErrorWith(string $message)
    {
        $this->assertResponseStatus(Response::HTTP_OK);

        //success GraphQL response should not contains errors
        if ($this->client->getGraphQL()) {
            if ($this->isValidGraphQLResponse() && $errors = $this->getGraphQLResponseError()) {
                $errorsStack = '';
                foreach ($errors as $error) {
                    $errorsStack .= $error->message."\n";
                }

                Assert::assertContains($message, $errorsStack);
            } else {
                $this->graphQLContext->debugLastQuery();
                throw new AssertionFailedError('The response is not the expected error response.');
            }
        }
    }

    /**
     * Assert that latest response is a GraphQL error with the given code
     *
     * @Then /^the response is GraphQL error with code "([^"]*)"$/
     */
    public function theResponseIsGraphQLErrorWithCode(string $code)
    {
        $this->assertResponseStatus(Response::HTTP_OK);

        //success GraphQL response should not contains errors
        if ($this->client->getGraphQL()) {
            if ($this->isValidGraphQLResponse() && $errors = $this->getGraphQLResponseError()) {
                $errorsStack = '';
                foreach ($errors as $error) {
                    $errorsStack .= $error->code."\n";
                }

                Assert::assertContains($code, $errorsStack);
            } else {
                $this->graphQLContext->debugLastQuery();
                throw new AssertionFailedError('The response is not the expected error response.');
            }
        }
    }

    /**
     * @Then the response is OK
     */
    public function assertResponseIsOk()
    {
        $this->assertResponseStatus(Response::HTTP_OK);

        //success GraphQL response should not contains errors
        if ($this->client->getGraphQL()) {
            if (!$this->isValidGraphQLResponse() || $this->getGraphQLResponseError()) {
                $this->graphQLContext->debugLastQuery();
                throw new AssertionFailedError('The response is not OK, invalid response or contains some error.');
            }
        }
    }

    protected function isValidGraphQLResponse()
    {
        $content = $this->client->getResponse()->getContent();
        Assert::assertJson((string) $content, 'Invalid server response');
        $response = json_decode($content, true);

        return $response && (isset($response['errors']) || isset($response['data']));
    }

    protected function getGraphQLResponseError()
    {
        if ($this->isValidGraphQLResponse()) {
            return Json::getValue($this->client->getResponse(), 'errors');
        }

        return null;
    }
}

