<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServicesCompilerPass;
use Ynlo\GraphQLBundle\DependencyInjection\YnloGraphQLExtension;
use Ynlo\GraphQLBundle\Type\Loader\TypeAutoLoader;

/**
 * Class YnloGraphQLBundle
 */
class YnloGraphQLBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $this->container->get(TypeAutoLoader::class)->autoloadTypes();
    }

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TaggedServicesCompilerPass());
    }

    /**
     * {@inheritDoc}
     */
    public function getContainerExtension()
    {
        return new YnloGraphQLExtension();
    }
}
