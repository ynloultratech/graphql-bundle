<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use PHPUnit\Util\Blacklist;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Test\Assert\DoctrineAssertTrait;
use Ynlo\GraphQLBundle\Test\Assert\JsonAssertTrait;
use Ynlo\GraphQLBundle\Test\Assert\ResponseAssertTrait;
use Ynlo\GraphQLBundle\Test\FixtureLoader\FixtureLoader;
use Ynlo\GraphQLBundle\Test\Helper\DoctrineHelperTrait;
use Ynlo\GraphQLBundle\Test\Helper\JsonHelperTrait;
use Ynlo\GraphQLBundle\Test\Helper\ResponseHelperTrait;

/**
 * Class ApiTestCase
 */
class ApiTestCase extends WebTestCase
{
    // helpers
    use DoctrineHelperTrait;
    use JsonHelperTrait;
    use ResponseHelperTrait;

    // asserts
    use DoctrineAssertTrait;
    use JsonAssertTrait;
    use ResponseAssertTrait;

    /**
     * @var ReferenceRepository
     */
    protected static $referenceRepository;

    /**
     * @var Client
     */
    protected static $client;

    /**
     * Whether to insulate each request or not.
     * The request ran into a separate process to avoid
     * any conflict (cache, container, doctrine etc..) with previous requests.
     *
     * NOTE: should be disabled for debugging
     *
     * @var bool
     */
    protected static $insulateRequests = true;

    /**
     * Endpoint where the API is available, e.g. /api
     *
     * @var string
     */
    protected static $endpoint = '';

    private static $lastQuery;

    private static $lastQueryExecutionTime = 0;

    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        Blacklist::$blacklistedClassNames['Ynlo\GraphQLBundle\Test\ApiTestCase'] = 1;
    }

    final public static function setUpBeforeClass()
    {
        static::$client = null;
    }

    final public function setUp()
    {
        parent::setUp();

        self::$lastQuery = null;
        self::$lastQueryExecutionTime = 0;

        static::loadFixtures();
        $this->before();
    }

    /**
     * This method is called before a test is executed.
     */
    public function before()
    {

    }

    /**
     * {@inheritdoc}
     */
    final protected function tearDown()
    {
        parent::tearDown();
        static::$client = null;
        $this->after();
    }

    /**
     * This method is called after a test is executed.
     */
    public function after()
    {

    }

    protected static function createClient(array $options = [], array $server = [])
    {
        return static::$client = parent::createClient($options, $server);
    }

    protected static function getClient(): Client
    {
        return static::$client ?? static::createClient();
    }

    protected static function loadFixtures($classNames = [])
    {
        /** @var Client $client */
        $client = static::getClient();
        $container = $client->getContainer();
        if ($container) {
            $fixtureLoader = new FixtureLoader($container, $container->get('doctrine'));
            static::$referenceRepository = $fixtureLoader->loadFixtures($classNames);
        }
    }

    protected static function getFixtureReference(string $name)
    {
        return self::$referenceRepository->getReference($name);
    }

    /**
     * @param string    $query     query or mutation in GraphQL format
     * @param array     $variables array of variables
     * @param bool|null $insulate  Whether to insulate the requests or not. Leave null to use default value.
     *                             The request ran into a separate process to avoid
     *                             any conflict (cache, container, doctrine etc..) with previous requests.
     *                             NOTE: should be disabled for debugging
     *
     * @return Response
     */
    protected static function send($query, array $variables = [], $insulate = null): Response
    {
        self::$lastQuery = ['query' => $query, 'variables' => $variables];

        if (null === $insulate) {
            $insulate = static::$insulateRequests;
        }

        $client = static::getClient();
        $client->insulate($insulate);

        $watch = new Stopwatch();
        $watch->start('query');
        $client->request(Request::METHOD_POST, self::$endpoint, [], [], [], json_encode(self::$lastQuery));
        $watch->stop('query');
        self::$lastQueryExecutionTime = $watch->getEvent('query')->getDuration();

        return $client->getResponse();
    }

    /**
     * @param string               $nodeType
     * @param string|NodeInterface $databaseId
     *
     * @return string
     */
    protected static function encodeID($nodeType, $databaseId)
    {
        if ($databaseId instanceof NodeInterface) {
            $databaseId = $databaseId->getId();
        }

        return ID::encode($nodeType, $databaseId);
    }

    protected static function decodeID(string $globalID): ID
    {
        return ID::createFromString($globalID);
    }

    /**
     * Print helpful debug information for latest executed query
     */
    protected static function debugLastQuery()
    {
        if (self::$lastQuery) {
            $query = self::$lastQuery['query'] ?? null;

            $type = 'QUERY';
            if (preg_match('/^\s*mutation/', $query)) {
                $type = 'MUTATION';
            }

            $variables = self::$lastQuery['variables'] ?? null;

            print_r("\033[43m\n\n----------------------- $type ---------------------\n\n\033[0m");
            print_r($query ?? null);
            print_r("\n\n");
            print_r("\033[46m------------------- VARIABLES-----------------------\n\n\033[0m");
            print_r(json_encode($variables, JSON_PRETTY_PRINT));
            print_r("\n\n");

            /** @var Response $response */
            $response = static::getClient()->getResponse();
            $bg = $response->getStatusCode() >= 400 ? 41: 42;
            print_r("\033[{$bg}m-------------------- RESPONSE ----------------------\n\n\033[0m");
            print_r(sprintf("STATUS: [%s] %s \n", $response->getStatusCode(), Response::$statusTexts[$response->getStatusCode()] ?? 'Unknown Status'));
            print_r(sprintf("TIME: %s ms \n\n", self::$lastQueryExecutionTime));

            $content = $response->getContent();
            $json = @json_decode($content, true);
            if ($json) {
                print_r(json_encode($json, JSON_PRETTY_PRINT));
            } else {
                print_r($content);
            }
            print_r("\n\n");
            print_r("-----------------------------------------------------\n\n");
        } else {
            throw new \RuntimeException('Does not exist any executed query on current test, try use this method after "send" the query.');
        }
    }

    protected function runTest()
    {
        try {
            parent::runTest();
        } catch (\Exception $exception) {
            if (self::$lastQuery) {
                self::debugLastQuery();
            }

            throw $exception;
        }
    }
}

