<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Query\Node;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use GraphQL\Error\Error;
use Ynlo\GraphQLBundle\Definition\EnumDefinition;
use Ynlo\GraphQLBundle\Definition\EnumValueDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Plugin\PaginationDefinitionPlugin;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Filter\FilterInterface;
use Ynlo\GraphQLBundle\Model\ConnectionInterface;
use Ynlo\GraphQLBundle\Model\NodeConnection;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\OrderBy\Common\OrderBySimpleField;
use Ynlo\GraphQLBundle\OrderBy\OrderByContext;
use Ynlo\GraphQLBundle\OrderBy\OrderByInterface;
use Ynlo\GraphQLBundle\Pagination\DoctrineCursorPaginatorInterface;
use Ynlo\GraphQLBundle\Pagination\DoctrineOffsetCursorPaginator;
use Ynlo\GraphQLBundle\Pagination\PaginationRequest;
use Ynlo\GraphQLBundle\Util\FieldOptionsHelper;

/**
 * Base class to fetch nodes
 */
class AllNodesWithPagination extends AllNodes
{
    /**
     * @param array[] $args
     *
     * @return mixed
     *
     * @throws Error
     */
    public function __invoke($args = [])
    {
        //keep orderBy for BC
        $orderBy = array_merge($args['orderBy'] ?? [], $args['order'] ?? []);
        $first = $args['first'] ?? null;
        $last = $args['last'] ?? null;
        $before = $args['before'] ?? null;
        $after = $args['after'] ?? null;
        $page = $args['page'] ?? null;
        $search = $args['search'] ?? null;
        $filters = $args['filters'] ?? null;
        $where = $args['where'] ?? null;

        $this->initialize();

        $qb = $this->createQuery();
        $this->applyOrderBy($qb, $orderBy);

        if ($this->getContext()->getRoot()) {
            $this->applyFilterByParent($qb, $this->getContext()->getRoot());
        }

        if ($search) {
            $this->search($qb, $search);
        }

        if ($filters) {
            $this->applyFilters($qb, $filters);
        }

        if ($where) {
            $this->applyWhere($qb, $where);
        }

        $this->configureQuery($qb);
        foreach ($this->extensions as $extension) {
            $extension->configureQuery($qb, $this, $this->context);
        }

        if (!$first && !$last) {
            $error = sprintf('You must provide a `first` or `last` value to properly paginate records in "%s" connection.', $this->queryDefinition->getName());
            throw new Error($error);
        }

        if ($this->queryDefinition->hasMeta('pagination')) {
            $limitAllowed = $this->queryDefinition->getMeta('pagination')['limit'];

            if ($first > $limitAllowed || $last > $limitAllowed) {
                $current = $first ?? $last;
                $where = $first ? 'first' : 'last';
                $error = sprintf(
                    'Requesting %s records for `%s` exceeds the `%s` limit of %s records for "%s" connection',
                    $current,
                    $this->queryDefinition->getName(),
                    $where,
                    $limitAllowed,
                    $this->queryDefinition->getName()
                );
                throw new Error($error);
            }
        }

        $paginator = $this->createPaginator();

        $connection = $this->createConnection();
        $paginator->paginate($qb, new PaginationRequest($first, $last, $after, $before, $page), $connection);

        return $connection;
    }

    protected function createConnection(): ConnectionInterface
    {
        return new NodeConnection();
    }

    protected function createPaginator(): DoctrineCursorPaginatorInterface
    {
        return new DoctrineOffsetCursorPaginator();
    }

    /**
     * Apply advanced filters
     *
     * @deprecated since v1.1, `applyWhere` should be used instead
     */
    protected function applyFilters(QueryBuilder $qb, array $filters)
    {
        $definition = $this->objectDefinition;
        foreach ($filters as $field => $value) {
            if (!$definition->hasField($field) || !$prop = $definition->getField($field)->getOriginName()) {
                continue;
            }

            $entityField = sprintf('%s.%s', $this->queryAlias, $prop);

            switch (gettype($value)) {
                case 'string':
                    $qb->andWhere($qb->expr()->eq($entityField, $qb->expr()->literal($value)));
                    break;
                case 'integer':
                case 'double':
                    $qb->andWhere($qb->expr()->eq($entityField, $value));
                    break;
                case 'boolean':
                    $qb->andWhere($qb->expr()->eq($entityField, (int) $value));
                    break;
                case 'array':
                    if (empty($value)) {
                        $qb->andWhere($qb->expr()->isNull($entityField));
                    } else {
                        $qb->andWhere($qb->expr()->in($entityField, $value));
                    }
                    break;
                case 'NULL':
                    $qb->andWhere($qb->expr()->isNull($entityField));
                    break;
            }
        }
    }

    /**
     * @param QueryBuilder    $qb
     * @param array|OrderBy[] $orderBy
     *
     * @throws Error
     */
    protected function applyOrderBy(QueryBuilder $qb, $orderBy)
    {
        if (!$this->getContext()->getDefinition()->hasArgument('order')) {
            return;
        }

        $orderByType = $this->getContext()->getDefinition()->getArgument('order')->getType();

        /** @var InputObjectDefinition $orderByDefinition */
        $orderByDefinition = $this->getContext()->getEndpoint()->getType($orderByType);
        $orderByFieldName = $orderByDefinition->getField('field')->getType();
        /** @var EnumDefinition $orderByFieldDefinition */
        $orderByFieldDefinition = $this->getContext()->getEndpoint()->getType($orderByFieldName);
        /** @var ObjectDefinition $node */
        $node = $this->getContext()->getEndpoint()->getType($this->getContext()->getDefinition()->getNode());

        foreach ($orderBy as $order) {
            /** @var EnumValueDefinition $enumValueDeifinition */
            $enumValueDefinition = $orderByFieldDefinition->getValues()[$order->getField()];
            $orderByResolver = $enumValueDefinition->getMeta('resolver', OrderBySimpleField::class);

            //set with local name
            $order->setField($enumValueDefinition->getMeta('field', $order->getField()));

            /** @var OrderByInterface $orderByInstance */
            $orderByInstance = (new \ReflectionClass($orderByResolver))->newInstanceWithoutConstructor();

            if ($order->getField() && $node->hasField($order->getField())) {
                $relatedField = $node->getField($order->getField());
                $context = new OrderByContext($this->getContext(), $node, $relatedField);
            } else {
                $context = new OrderByContext($this->getContext(), $node);
            }

            $orderByInstance($context, $qb, $this->queryAlias, $order);
        }
    }

    /**
     * @param QueryBuilder    $qb
     * @param array           $where
     *
     * @throws \ReflectionException
     */
    protected function applyWhere(QueryBuilder $qb, array $where): void
    {
        $whereType = $this->getContext()->getDefinition()->getArgument('where')->getType();

        /** @var InputObjectDefinition $whereDefinition */
        $whereDefinition = $this->getContext()->getEndpoint()->getType($whereType);

        /** @var ObjectDefinition $node */
        $node = $this->getContext()->getEndpoint()->getType($this->getContext()->getDefinition()->getNode());

        foreach ($where as $filterName => $condition) {
            $filterDefinition = $whereDefinition->getField($filterName);

            /** @var FilterInterface $filter */
            if ($this->container->has($filterDefinition->getResolver())) {
                $filter = $this->container->get($filterDefinition->getResolver());
            } else {
                $filter = (new \ReflectionClass($filterDefinition->getResolver()))->newInstanceWithoutConstructor();
            }

            $fieldName = $filterDefinition->getMeta('filter_field');
            if ($fieldName && $node->hasField($fieldName)) {
                $relatedField = $node->getField($fieldName);
                $filterContext = new FilterContext($this->getContext(), $node, $relatedField);
            } else {
                $filterContext = new FilterContext($this->getContext(), $node);
            }

            $filter($filterContext, $qb, $condition);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $search
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function search(QueryBuilder $qb, string $search): void
    {
        $query = $this->queryDefinition;
        $node = $this->objectDefinition;

        $em = $this->getManager();
        $metadata = $em->getClassMetadata($this->entity);

        $node->getFields();
        $columns = [];
        $searchFields = FieldOptionsHelper::normalize($query->getMeta('pagination')['search_fields'] ?? ['*']);
        foreach ($node->getFields() as $field) {
            if (!FieldOptionsHelper::isEnabled($searchFields, $field->getName())) {
                continue;
            }

            $config = FieldOptionsHelper::getConfig($searchFields, $field->getName(), null);
            $searchColumn = null;
            if ($metadata->hasField($field->getName())) {
                $searchColumn = $field->getName();
            }

            if (!$searchColumn && $metadata->hasField($field->getOriginName())) {
                $searchColumn = $field->getOriginName();
            }

            if ($searchColumn) {
                switch ($metadata->getFieldMapping($searchColumn)['type']) {
                    case Type::STRING:
                    case Type::TEXT:
                        $columns[$searchColumn] = $config ?? 'partial';
                        break;
                    case Type::INTEGER:
                    case Type::BIGINT:
                    case Type::FLOAT:
                    case Type::DECIMAL:
                    case Type::SMALLINT:
                        $columns[$searchColumn] = $config ?? 'exact';
                        break;
                }
            }
        }

        foreach ($searchFields as $field => $mode) {
            if (\is_int($field)) {
                $field = $mode;
                $mode = 'exact';
            }

            if ('*' === $field || $mode === false) {
                continue;
            }
            $columns[$field] = $mode;
        }


        if (\count($columns) > 0) {
            $joins = [];
            $orx = new Orx();
            foreach ($columns as $column => $mode) {
                $alias = $qb->getRootAliases()[0];
                while (strpos($column, '.') !== false) {
                    [$child, $column] = explode('.', $column, 2);
                    $parentAlias = $alias;
                    $alias = 'searchJoin'.ucfirst($child);
                    if (!\in_array($alias, $joins, true)) {
                        $qb->leftJoin("{$parentAlias}.{$child}", $alias);
                        $joins[] = $alias;
                    }
                }

                if ('partial' === $mode) {
                    //search each word separate
                    $searchArray = explode(' ', $search);

                    $partialAnd = new Andx();
                    foreach ($searchArray as $index => $q) {
                        $partialAnd->add("$alias.$column LIKE :query_search_$index");
                        $qb->setParameter("query_search_$index", '%'.addcslashes($q, '%_').'%');
                    }
                    $orx->add($partialAnd);
                } else {
                    $orx->add("$alias.$column LIKE :query_search");
                    $qb->setParameter('query_search', trim($search));
                }
            }

            $qb->andWhere($orx);
        }
    }

    /**
     * @param QueryBuilder  $qb
     * @param NodeInterface $root
     */
    protected function applyFilterByParent(QueryBuilder $qb, NodeInterface $root)
    {
        $parentField = null;
        if ($this->queryDefinition->hasMeta('pagination')) {
            $parentField = $this->queryDefinition->getMeta('pagination')['parent_field'] ?? null;
        }
        if (!$parentField) {
            throw new \RuntimeException(
                sprintf(
                    'Missing parent field to filter "%s" by given parent.
             The "parentField" should be specified in @Pagination annotation.',
                    $this->queryDefinition->getName()
                )
            );
        }

        if ($this->objectDefinition->hasField($parentField)) {
            $parentField = $this->objectDefinition->getField($parentField)->getOriginName();
        }

        $paramName = 'root'.mt_rand();
        if ($this->queryDefinition->getMeta('pagination')['parent_relation'] === PaginationDefinitionPlugin::MANY_TO_MANY) {
            $qb->andWhere(sprintf(':%s MEMBER OF %s.%s', $paramName, $this->queryAlias, $parentField))
                ->setParameter($paramName, $root);
        } else {
            $qb->andWhere(sprintf('%s.%s = :%s', $this->queryAlias, $parentField, $paramName))
                ->setParameter($paramName, $root);
        }
    }
}
