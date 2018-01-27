<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Fixtures;

use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Test\FixtureLoader\FixtureLoader;

class LoadFixturesSubscriber implements EventSubscriberInterface
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var FixtureManager
     */
    protected $fixtureManager;

    /**
     * LoadFixturesSubscriber constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel, FixtureManager $fixtureManager)
    {
        $this->kernel = $kernel;
        $this->fixtureManager = $fixtureManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScenarioTested::BEFORE => 'loadFixtures',
        ];
    }

    public function loadFixtures()
    {
        $container = $this->kernel->getContainer();
        $registry = $container->get('doctrine');
        $fixturesLoader = new FixtureLoader($container, $registry);
        $this->fixtureManager->setRepository($fixturesLoader->loadFixtures());
    }
}