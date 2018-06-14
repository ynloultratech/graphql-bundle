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

@trigger_error("TaggedServices component has been deprecated since v1.1 and will be deleted in v2.0, use symfony tag injection instead \"!tagged tag_name\"", E_USER_DEPRECATED);

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @deprecated since v1.1 and will be deleted in v2.0, use symfony tag injection instead "!tagged tag_name"
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
        if (!$container->hasDefinition(TaggedServices::class)) {
            return;
        }

        $manager = $container->getDefinition(TaggedServices::class);

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
