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
use Ynlo\GraphQLBundle\DependencyInjection\Compiler\ControllerPass;
use Ynlo\GraphQLBundle\DependencyInjection\YnloGraphQLExtension;
use Ynlo\GraphQLBundle\Encoder\IDEncoderManager;
use Ynlo\GraphQLBundle\Type\Loader\TypeAutoLoader;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;
use Ynlo\GraphQLBundle\Util\IDEncoder;

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
        TypeRegistry::clear(); //required for tests

        $this->container->get(TypeAutoLoader::class)->autoloadTypes();

        //setup the encoder to use statically
        IDEncoder::setup($this->container->get(IDEncoderManager::class)->getEncoder());
    }

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TaggedServicesCompilerPass());
        $container->addCompilerPass(new ControllerPass());
    }

    /**
     * {@inheritDoc}
     */
    public function getContainerExtension()
    {
        return new YnloGraphQLExtension();
    }
}
