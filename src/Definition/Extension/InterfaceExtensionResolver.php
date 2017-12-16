<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\HasExtensionsInterface;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Extension\ExtensionInterface;
use Ynlo\GraphQLBundle\Util\ClassUtils;

/**
 * InterfaceExtensionResolver
 */
class InterfaceExtensionResolver extends AbstractDefinitionExtension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'extension';
    }

    /**
     * {@inheritDoc}
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config)
    {
        if (!$definition instanceof InterfaceDefinition || !$definition->getImplementors()) {
            return;
        }

        $bundleNamespace = ClassUtils::relatedBundleNamespace($definition->getClass());
        $extensionClass = ClassUtils::applyNamingConvention($bundleNamespace, 'Extension', null, $definition->getName().'Extension');
        if (class_exists($extensionClass)) {
            if ($this->container->has($extensionClass)) {
                $extension = $this->container->get($extensionClass);
            } else {
                $extension = new $extensionClass();
            }

            /** @var ExtensionInterface $extension */
            $priority = $extension->getPriority();

            foreach ($definition->getImplementors() as $implementor) {
                $object = $endpoint->getType($implementor);
                if ($object instanceof HasExtensionsInterface) {
                    $object->addExtension($extensionClass, $priority);
                }
            }
        }
    }
}
