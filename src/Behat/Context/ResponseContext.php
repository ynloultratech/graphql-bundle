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
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareInterface;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareTrait;

/**
 * Context to work with latest response
 */
final class ResponseContext implements Context, ClientAwareInterface
{
    use ClientAwareTrait;

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
     * @Then the response is OK
     */
    public function assertResponseIsOk()
    {
        $this->assertResponseStatus(Response::HTTP_OK);
    }
}