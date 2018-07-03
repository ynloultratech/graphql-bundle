<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter\Resolver;

use Ynlo\GraphQLBundle\Annotation\Filter;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\FilterResolverInterface;

class CustomGenericFilterResolver implements FilterResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(ObjectDefinitionInterface $node, Endpoint $endpoint): array
    {
        // TODO: Implement resolve() method.
    }
}
