<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter;

use Ynlo\GraphQLBundle\Annotation\Filter;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

interface FilterResolverInterface
{
    /**
     * Resolve filter definitions
     *
     * @param ExecutableDefinitionInterface $executableDefinition
     * @param ObjectDefinitionInterface     $node
     * @param Endpoint                      $endpoint
     *
     * @return Filter[]
     */
    public function resolve(ExecutableDefinitionInterface $executableDefinition, ObjectDefinitionInterface $node, Endpoint $endpoint): array;
}
