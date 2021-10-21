<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Ynlo\GraphQLBundle\Extension\ExtensionInterface;
use Ynlo\GraphQLBundle\Extension\ExtensionsAwareInterface;

/**
 * AbstractResolver is a simple implementation of a Resolver.
 *
 * It provides methods to common features needed in resolvers.
 */
abstract class AbstractResolver implements ResolverInterface, ExtensionsAwareInterface
{
    use ContainerAwareTrait;

    protected ?ResolverContext $context = null;

    /**
     * @var ExtensionInterface[]
     */
    protected $extensions = [];

    protected ?ResolverServices $services = null;

    /**
     * {@inheritDoc}
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @return ResolverContext
     */
    public function getContext(): ResolverContext
    {
        return $this->context;
    }

    /**
     * @param ResolverContext $context
     */
    public function setContext(ResolverContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param ResolverServices $services
     */
    public function setServices(ResolverServices $services): void
    {
        $this->services = $services;
    }

    /**
     * Gets a container service by its id.
     *
     * @param string $id The service id
     *
     * @return mixed The service
     */
    protected function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * @param string|null $name
     *
     * @return ObjectManager|EntityManagerInterface
     */
    protected function getManager(string $name = null): ObjectManager
    {
        return $this->services->getDoctrine()->getManager($name);
    }

    /**
     * @return EventDispatcherInterface|null
     */
    protected function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->services->getEventDispatcher();
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator(): ValidatorInterface
    {
        return $this->services->getValidator();
    }

    /**
     * Creates and returns a Form Builder instance from the type of the form.
     *
     * @param string $type    The fully qualified class name of the form type
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     *
     * @return FormBuilderInterface
     */
    protected function createFormBuilder($type, $data = null, array $options = [])
    {
        return $this->services->getFormFactory()->createBuilder($type, $data, $options);
    }
}
