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
use Ynlo\GraphQLBundle\Util\FieldOptionsHelper;
use Ynlo\GraphQLBundle\Util\TypeUtil;

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
        if (($pagination = $executableDefinition->getMeta('pagination')) && $options = $pagination['filters'] ?? ['*']) {
            foreach ($filters as $index => $filter) {
                if (!FieldOptionsHelper::isEnabled($options, $filter->name)) {
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
                $field->setType(TypeUtil::normalize($filter->type));
                $field->setList(TypeUtil::isTypeList($filter->type));
                $field->setNonNullList(TypeUtil::isTypeNonNullList($filter->type));
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
