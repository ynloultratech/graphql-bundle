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

    private static $client;

    /**
     * Whether the client should be cleared after each test
     *
     * @var bool
     */
    protected $cleanup = true;

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
    public static function setUpBeforeClass()
    {
        self::$client = null;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        self::loadFixtures();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if ($this->cleanup) {
            self::$client = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected static function createClient(array $options = [], array $server = [])
    {
        $client = parent::createClient($options, $server);
        self::$client = $client;

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getClient(): Client
    {
        return self::$client ?? static::createClient();
    }
}

