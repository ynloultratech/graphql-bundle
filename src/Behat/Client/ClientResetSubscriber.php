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

use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Restart the client before every scenario
 */
class ClientResetSubscriber implements EventSubscriberInterface
{
    /**
     * @var GraphQLClient
     */
    protected $client;

    public function __construct(GraphQLClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScenarioTested::BEFORE => 'restartClient',
        ];
    }

    public function restartClient()
    {
        $this->client->restart();
    }
}