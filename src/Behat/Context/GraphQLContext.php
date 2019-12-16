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
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareInterface;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareTrait;
use Ynlo\GraphQLBundle\Behat\Gherkin\YamlStringNode;

/**
 * Context to work with GraphQL
 * send queries, mutations etc
 *
 * @property File $currentFeatureFile
 */
final class GraphQLContext implements Context, ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @var File
     */
    private static $currentFeatureFile;

    /**
     * @BeforeFeature
     */
    public static function prepareGraphQLContext(BeforeFeatureScope $scope)
    {
        self::$currentFeatureFile = new File($scope->getFeature()->getFile());
    }

    /**
     * @AfterStep
     */
    public function after(AfterStepScope $scope)
    {
        if (!$scope->getTestResult()->isPassed()) {
            if ($this->client->getResponse() && $this->client->getResponse()->getStatusCode() >= 400) {
                $this->debugLastQuery();
            }
        }
    }

    /**
     * Set GraphQL operation
     *
     * Example: Given the operation:
     *          """
     *          query($id: ID!){
     *              node(id: $id) {
     *                  id
     *                  ... on Post {
     *                      title
     *                      body
     *                  }
     *              }
     *          }
     *          """
     *
     * @Given /^the operation:$/
     */
    public function theOperation(PyStringNode $string)
    {
        $this->client->setGraphQL($string->getRaw());
    }

    /**
     * Find for a file to read the GraphQL query
     * the file must not contains more than one query
     *
     * Example: Given the operation in file 'some_query.graphql'
     *
     * @Given /^the operation in file "([^"]*)"$/
     */
    public function theOperationInFile($filename)
    {
        $queryFile = sprintf('%s%s%s', self::$currentFeatureFile->getPath(), DIRECTORY_SEPARATOR, $filename);
        if (file_exists($queryFile)) {
            $file = new File($queryFile);
            $this->client->setGraphQL(file_get_contents($file->getPathname()));
        } else {
            throw new FileNotFoundException(null, 0, null, $queryFile);
        }
    }

    /**
     * Find for specific query name in given file.
     * The file can contain multiple named queries.
     *
     * Example: Given the operation named "GetUser" in file 'queries.graphql'
     *
     * @Given /^the operation named "([^"]*)" in file "([^"]*)"$/
     */
    public function theOperationNamedInFile($queryName, $file)
    {
        // TODO: add support for fragments
        // if a fragment is not used in some operation in the same file a error is thrown

        $this->theOperationInFile($file);
        $this->operationName = $queryName;
        if ($this->client->getGraphQL()) {
            // remove non necessary operations to avoid errors with unsettled variables
            $pattern = '/(query|mutation|subscription)\s+(?!'.$queryName.'\s*[\({])(.+\n)+}\n*/';
            $this->client->setGraphQL(preg_replace($pattern, null, $this->client->getGraphQL()));
        }

        if ($queryName) {
            if (strpos($this->client->getGraphQL(), $queryName) === false) {
                throw new \RuntimeException(sprintf('Does not exist any operation called "%s" in "%s"', $queryName, $file));
            }
        }
    }

    /**
     * Find for specific query name in file with the same name of current feature.
     * e.g. some_feature.feature => some_query.graphql
     *
     * The file can contain multiple named queries.
     *
     * Example: Given the operation named "GetUser"
     *
     * @Given /^the operation named "([^"]*)"$/
     */
    public function theOperationNamed($queryName)
    {
        $queryFilename = str_replace('.feature', '.graphql', self::$currentFeatureFile->getBasename());
        $this->theOperationNamedInFile($queryName, $queryFilename);
    }

    /**
     * @When send
     */
    public function send()
    {
        $this->client->sendQuery();
    }

    /**
     * Set query variable with scalar value before run the given query
     *
     * Example: And variable "username" is "admin"
     * Example: And variable "limit" is 2
     * Example: And variable "visible" is true
     * Example: And variable "orderBy" is { [{field:'login', direction: 'DESC'}] }
     *
     * @Given /^variable "([^"]*)" is "?([^"]*)"?$/
     */
    public function setVariableEqualTo($path, $value)
    {
        $accessor = new PropertyAccessor();
        $variables = $this->client->getVariables();
        $accessor->setValue($variables, sprintf("[%s]", $path), $value);
        $this->client->setVariables($variables);
    }

    /**
     * Allow set multiple variables using YAML syntax
     *
     * Example:
     * And variables:
     *       """
     *       input:
     *          clientMutationId: "'{faker.randomNumber}'"
     *          status: PUBLISHED
     *          title: "{faker.sentence}"
     *          body: "{faker.paragraph}"
     *          tags: ['asd', 'asdsd']
     *          categories:
     *              - "#category1"
     *              - "#category2"
     *       """
     *
     * @Given /^variables:$/
     */
    public function variables(YamlStringNode $variables)
    {
        $this->client->setVariables($variables->toArray());
    }

    /**
     * Print helpful debug information for latest executed query
     *
     * @Then debug last query
     */
    public function debugLastQuery()
    {
        if ($this->client->getGraphQL()) {

            /** @var Response $response */
            $response = $this->client->getResponse();

            $content = $response->getContent();
            $json = @json_decode($content, true);

            $error = $response->getStatusCode() >= 400;
            if ($json && isset($json['errors'])) {
                $error = true;
            }

            $bg = $error ? 41 : 42;
            print_r("\n\n");
            print_r("\033[{$bg}m-------------------- RESPONSE ----------------------\033[0m\n\n");
            print_r(sprintf("STATUS: [%s] %s \n", $response->getStatusCode(), Response::$statusTexts[$response->getStatusCode()] ?? 'Unknown Status'));

            if ($json) {
                $output = json_encode($json, JSON_PRETTY_PRINT);
            } else {
                $output = $content;
            }

            print_r($output);

            print_r("\n\n");
            print_r("\033[46m------------------- VARIABLES-----------------------\033[0m\n\n");
            $variables = $this->client->getVariables() ?? null;
            print_r(json_encode($variables, JSON_PRETTY_PRINT));

            $query = $this->client->getGraphQL() ?? null;
            $type = 'QUERY';
            if (preg_match('/^\s*mutation/', $query)) {
                $type = 'MUTATION';
            }

            print_r("\n\n\033[43m----------------------- $type ---------------------\033[0m\n\n");
            print_r($query ?? null);
            print_r("\n\n");
            print_r("-----------------------------------------------------\n\n");
            ob_flush();
        } else {
            throw new \RuntimeException('Does not exist any executed query on current test, try use this method after "send" the query.');
        }
    }


}
