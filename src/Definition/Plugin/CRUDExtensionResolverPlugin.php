<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Plugin;

use Ynlo\GraphQLBundle\Definition\ClassAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\HasExtensionsInterface;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Util\ClassUtils;

/**
 * This plugin automatically load CRUD extensions
 * based on object interfaces and registered interfaces
 */
class CRUDExtensionResolverPlugin extends AbstractDefinitionPlugin
{
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
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config): void
    {
        if ($definition instanceof InterfaceDefinition && $definition->getImplementors()) {
            $this->resolveInterfaceExtension($definition, $endpoint);
        }

        if ($definition instanceof ClassAwareDefinitionInterface) {
            $this->resolveObjectRealInterfaceExtensions($definition);
        }
    }

    /**
     * Using naming convention resolve CRUD extension for given interface definition and automatically register this extension
     * for all interface implementors
     *
     * @param InterfaceDefinition $definition
     * @param Endpoint            $endpoint
     */
    protected function resolveInterfaceExtension(InterfaceDefinition $definition, Endpoint $endpoint): void
    {
        $bundleNamespace = ClassUtils::relatedBundleNamespace($definition->getClass());
        $extensionClass = ClassUtils::applyNamingConvention($bundleNamespace, 'Extension', null, $definition->getName().'Extension');
        if (class_exists($extensionClass)) {
            foreach ($definition->getImplementors() as $implementor) {
                $definition->addExtension($extensionClass);
                $object = $endpoint->getType($implementor);
                if ($object instanceof HasExtensionsInterface) {
                    $object->addExtension($extensionClass);
                }
            }
        }
    }

    /**
     * Using naming convention resolve all extensions for given object
     * based on implemented interfaces.
     *
     * This method use PHP real interfaces instead of registered interface types.
     *
     * @param ClassAwareDefinitionInterface $definition
     *
     * @throws \ReflectionException
     */
    protected function resolveObjectRealInterfaceExtensions(ClassAwareDefinitionInterface $definition): void
    {
        $class = $definition->getClass();

        if (class_exists($class)) {
            $refClass = new \ReflectionClass($definition->getClass());
            if ($interfaces = $refClass->getInterfaceNames()) {
                foreach ($interfaces as $interface) {
                    $bundleNamespace = ClassUtils::relatedBundleNamespace($interface);
                    if (preg_match('/(\w+)Interface?$/', $interface, $matches)) {
                        $extensionClass = ClassUtils::applyNamingConvention($bundleNamespace, 'Extension', null, $matches[1].'Extension');
                        if (class_exists($extensionClass)) {
                            if ($definition instanceof HasExtensionsInterface) {
                                $definition->addExtension($extensionClass);
                            }
                        }
                    }
                }
            }
        }
    }
}
