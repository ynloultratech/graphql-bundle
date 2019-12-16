<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Ynlo\GraphQLBundle\Subscription\SubscriptionManager;

class SubscriptionConsumerCommand extends Command
{
    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * SubscriptionConsumerCommand constructor.
     *
     * @param Kernel              $kernel
     * @param SubscriptionManager $subscriptionManager
     * @param PublisherInterface  $publisher
     */
    public function __construct(Kernel $kernel, SubscriptionManager $subscriptionManager, PublisherInterface $publisher)
    {
        parent::__construct();

        $this->kernel = $kernel;
        $this->subscriptionManager = $subscriptionManager;
        $this->publisher = $publisher;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('graphql:subscriptions:consume')
             ->setDescription('Listen for subscriptions to consume then and dispatch to mercure HUB.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->subscriptionManager->consume($output, $input->getOption('verbose'));
    }
}
