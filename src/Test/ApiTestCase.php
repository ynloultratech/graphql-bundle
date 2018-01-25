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

use PHPUnit\Util\Blacklist;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ApiTestCase
 */
class ApiTestCase extends WebTestCase
{
    use DataFixtureTrait;
    use RequestHelperTrait;
    use ResponseHelperTrait;
    use JsonHelperTrait;
    use DoctrineORMHelperTrait;
    use GraphQLHelperTrait;

    /**
     * @var Client
     */
    protected static $client;

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        Blacklist::$blacklistedClassNames['Ynlo\GraphQLBundle\Test\ApiTestCase'] = 1;
    }


    /**
     * {@inheritdoc}
     */
    final public static function setUpBeforeClass()
    {
        static::$client = null;
    }

    /**
     * {@inheritdoc}
     */
    final public function setUp()
    {
        parent::setUp();

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

    /**
     * {@inheritdoc}
     */
    protected static function createClient(array $options = [], array $server = [])
    {
        return static::$client = parent::createClient($options, $server);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getClient(): Client
    {
        return static::$client ?? static::createClient();
    }
}

