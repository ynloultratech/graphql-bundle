<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ynlo\GraphQLBundle\Controller\ExplorerController;

class ControllerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter('graphql.graphiql');
        $providerId = $container->getParameter('graphql.graphiql_auth_provider');
        $provider = null;

        if ($providerId) {
            $provider = $container->getDefinition($providerId);
        } elseif ($config['authentication']['required']) {
            throw new \RuntimeException('Configure a valid provider to use GraphiQL with authentication.');
        }

        $controllerDefinition = $container->getDefinition(ExplorerController::class);
        $controllerDefinition->setArgument(1, $provider);
    }
}
