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

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Ynlo\GraphQLBundle\Events\EventDispatcherAwareInterface;
use Ynlo\GraphQLBundle\Events\EventDispatcherAwareTrait;
use Ynlo\GraphQLBundle\Extension\ExtensionInterface;
use Ynlo\GraphQLBundle\Extension\ExtensionsAwareInterface;

/**
 * AbstractResolver is a simple implementation of a Resolver.
 *
 * It provides methods to common features needed in resolvers.
 */
abstract class AbstractResolver implements ResolverInterface, ExtensionsAwareInterface, EventDispatcherAwareInterface
{
    use ContainerAwareTrait;
    use EventDispatcherAwareTrait;

    /**
     * @var ResolverContext
     */
    protected $context;

    /**
     * @var ExtensionInterface[]
     */
    protected $extensions = [];

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
     * @return EntityManager
     */
    protected function getManager(): EntityManager
    {
        return $this->get('doctrine')->getManager();
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator(): ValidatorInterface
    {
        return $this->get('validator');
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
        return $this->container->get('form.factory')->createBuilder($type, $data, $options);
    }
}
