<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Action;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Ynlo\GraphQLBundle\Definition\ResolverContext;

/**
 * Interface APIActionInterface
 */
interface APIActionInterface extends ContainerAwareInterface
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
