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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Interface ResolverInterface
 */
interface ResolverInterface extends ContainerAwareInterface
{

    /**
     * @return ResolverContext
     */
    public function getContext(): ResolverContext;

    /**
     * @param ResolverContext $context
     */
    public function setContext(ResolverContext $context);
}
