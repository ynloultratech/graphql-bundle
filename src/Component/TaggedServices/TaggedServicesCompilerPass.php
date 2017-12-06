<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Component\TaggedServices;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class TaggedServicesCompilerPass
 */
class TaggedServicesCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tagged_services')) {
            return;
        }

        $manager = $container->getDefinition('tagged_services');

        $definitions = $container->getDefinitions();
        foreach ($definitions as $id => $definition) {
            foreach ($definition->getTags() as $tagName => $tagAttributes) {
                $manager->addMethodCall(
                    'addSpecification',
                    [$id, $tagName, $tagAttributes[0]]
                );
            }
        }
    }
}
