<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Behat\Context\KernelAwareContext;

/**
 * Kernel aware contexts initializer.
 * Sets Kernel instance to the KernelAware contexts.
 */
final class KernelAwareInitializer implements ContextInitializer, EventSubscriberInterface
{
    private KernelInterface $kernel;

    /**
     * Initializes initializer.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScenarioTested::AFTER => ['rebootKernel', -15],
            ExampleTested::AFTER => ['rebootKernel', -15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof KernelAwareContext && !$this->usesKernelDictionary($context)) {
            return;
        }

        $context->setKernel($this->kernel);
    }

    /**
     * Reboots HttpKernel after each scenario.
     */
    public function rebootKernel()
    {
        $this->kernel->shutdown();
        $this->kernel->boot();
    }

    /**
     * Checks whether the context uses the KernelDictionary trait.
     *
     * @param Context $context
     *
     * @return boolean
     */
    private function usesKernelDictionary(Context $context)
    {
        $refl = new \ReflectionObject($context);
        if (method_exists($refl, 'getTraitNames')
            && in_array('Ynlo\\GraphQLBundle\\Behat\\Context\\KernelDictionary', $refl->getTraitNames())) {
            return true;
        }

        return false;
    }
}