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
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

class FilterFactory
{
    /**
     * @var iterable|FilterResolverInterface[]
     */
    protected $resolvers;

    /**
     * FilterFactory constructor.
     *
     * @param iterable|FilterResolverInterface[] $resolvers
     */
    public function __construct($resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @param ExecutableDefinitionInterface $executableDefinition
     * @param ObjectDefinitionInterface     $node
     * @param Endpoint                      $endpoint
     */
    public function build(ExecutableDefinitionInterface $executableDefinition, ObjectDefinitionInterface $node, Endpoint $endpoint): void
    {
        if (!$executableDefinition->getNode()) {
            throw new \InvalidArgumentException('The given definition is not related to any valid node and can\'t be filtered');
        }

        $filters = [];
        foreach ($this->resolvers as $resolver) {
            $filters[] = $resolver->resolve($executableDefinition, $node, $endpoint);
        }

        /** @var Filter[] $filters */
        $filters = array_reverse(array_merge(... $filters));

        //unset resolved but not allowed filters
        if (($pagination = $executableDefinition->getMeta('pagination')) && $allowedFilters = $pagination['filters'] ?? []) {
            //normalize comma separated names
            foreach ($allowedFilters as $filtersNames => $option) {
                if (strpos($filtersNames, ',') !== false) {
                    $filtersNamesArray = explode(',', $filtersNames);
                    foreach ($filtersNamesArray as $name) {
                        $name = trim($name);
                        if (!isset($allowedFilters[$name])) {
                            $allowedFilters[$name] = $option;
                        }
                    }
                }
            }

            foreach ($filters as $index => $filter) {
                $allowed = $allowedFilters['*'] ?? \in_array('*', $allowedFilters, true);
                if (isset($allowedFilters[$filter->name])) {
                    $allowed = $allowedFilters[$filter->name];
                }
                if (!$allowed) {
                    unset($filters[$index]);
                }
            }
        }

        if (!$filters) {
            return;
        }

        $whereName = $executableDefinition->getNode().'Condition';
        if ($endpoint->hasType($whereName)) {
            $wheres = $endpoint->getType($whereName);
        } else {
            $wheres = new InputObjectDefinition();
            $wheres->setName($whereName);
            $endpoint->add($wheres);

            foreach ($filters as $filter) {
                if (!$filter->name || !$filter->type || !$filter->resolver) {
                    throw new \InvalidArgumentException('Invalid filter definition, "name", "type" and "class" are required.');
                }
                $field = new FieldDefinition();
                $field->setName($filter->name);
                $field->setType($filter->type);
                $field->setResolver($filter->resolver);
                if ($filter->field) {
                    $field->setMeta('filter_field', $filter->field);
                }
                $wheres->addField($field);
            }
        }

        $where = new ArgumentDefinition();
        $where->setName('where');
        $where->setType($wheres->getName());
        $where->setNonNull(false);
        $where->setDescription('Filter the list using conditions');

        $executableDefinition->addArgument($where);
    }
}
