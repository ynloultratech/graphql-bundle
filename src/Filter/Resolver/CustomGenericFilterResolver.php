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
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\FilterResolverInterface;

/**
 * Resolve generic filters manually settled in the list config
 *
 * Example:
 *
 * QueryList(
 *     filters={
 *      "*",
 *      "title, body": @GraphQL\Filter(resolver="App\Filter\LikeFilter", type="string")
 *     }
 * })
 */
class CustomGenericFilterResolver implements FilterResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(ExecutableDefinitionInterface $executableDefinition, ObjectDefinitionInterface $node, Endpoint $endpoint): array
    {
        $resolverFilters = [];
        if ($filters = $executableDefinition->getMeta('pagination')['filters'] ?? []) {
            foreach ($filters as $fields => $filter) {
                if ($filter instanceof Filter) {
                    $fields = explode(',', $fields);
                    foreach ($fields as $field) {
                        $filter = clone $filter;
                        $filter->name = $filter->field = trim($field);
                        $resolverFilters[] = $filter;
                    }
                }
            }
        }

        return $resolverFilters;
    }
}
