<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Tests;

use Ynlo\GraphQLBundle\Demo\AppBundle\DataFixtures\ORM\Fixtures;
use Ynlo\GraphQLBundle\Test\ApiTestCase as BaseApiTestCase;

/**
 * @deprecated this class has been deprecated, all tests should be moved to Behat
 */
abstract class ApiTestCase extends BaseApiTestCase
{
    private $token;

    /**
     * This method is called before a test is executed.
     */
    public function before()
    {
        if (!$this->token) {
            $this->token = $this
                ->getClient()
                ->getContainer()
                ->get('lexik_jwt_authentication.jwt_manager')
                ->create($this->getFixtureReference(Fixtures::USER_ADMIN));
        }

        $this->getClient()->setServerParameter('HTTP_Authorization', 'Bearer '.$this->token);
    }
}
