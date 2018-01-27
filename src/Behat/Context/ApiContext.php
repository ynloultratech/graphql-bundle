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
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareInterface;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareTrait;
use Ynlo\GraphQLBundle\Behat\Storage\StorageAwareInterface;
use Ynlo\GraphQLBundle\Behat\Storage\StorageAwareTrait;

/**
 * Should be used as base class for API tests,
 * provide some helpful methods and direct access to de client
 */
class ApiContext implements Context, ClientAwareInterface, StorageAwareInterface
{
    use ClientAwareTrait;
    use StorageAwareTrait;

    public function getRepose(): Response
    {
        return $this->client->getResponse();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->client->getContainer();
    }

    public function getDoctrine(): Registry
    {
        return $this->getContainer()->get('doctrine');
    }
}
