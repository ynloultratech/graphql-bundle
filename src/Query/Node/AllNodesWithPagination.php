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

use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use GraphQL\Error\Error;
use Ynlo\GraphQLBundle\Definition\Plugin\PaginationDefinitionPlugin;
use Ynlo\GraphQLBundle\Model\ConnectionInterface;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeConnection;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Pagination\DoctrineCursorPaginatorInterface;
use Ynlo\GraphQLBundle\Pagination\DoctrineOffsetCursorPaginator;
use Ynlo\GraphQLBundle\Pagination\PaginationRequest;

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
        $orderBy = $args['orderBy'] ?? [];
        $first = $args['first'] ?? null;
        $last = $args['last'] ?? null;
        $before = $args['before'] ?? null;
        $after = $args['after'] ?? null;
        $search = $args['search'] ?? null;
        $filters = $args['filters'] ?? null;

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
        $paginator->paginate($qb, new PaginationRequest($first, $last, $after, $before), $connection);

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
                    foreach ($value as &$val) {
                        if ($val instanceof ID) {
                            $val = (int) $val->getDatabaseId();
                        }
                    }
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
     * Filter some columns with simple string.
     */
    protected function search(QueryBuilder $qb, string $search)
    {
        //search every word separate
        $searchArray = explode(' ', $search);

        $alias = $qb->getRootAliases()[0];

        //TODO: allow some config to customize search fields
        $em = $this->getManager();
        $metadata = $em->getClassMetadata($this->entity);
        $searchFields = $metadata->getFieldNames();

        if (count($searchFields) > 0) {
            $meta = $qb->getEntityManager()->getClassMetadata($qb->getRootEntities()[0]);
            foreach ($searchArray as $q) {
                $q = trim(rtrim($q));
                $id = md5($q);
                $orx = new Orx();
                foreach ($searchFields as $field) {
                    if (strpos($field, '.') !== false && !isset($meta->embeddedClasses[explode('.', $field)[0]])) {
                        $orx->add("$field LIKE :search_$id");
                    } else { //append current alias
                        $orx->add("$alias.$field LIKE :search_$id");
                    }
                }
                $qb->andWhere($orx);
                $qb->setParameter("search_$id", "%$q%");
            }
        }
    }

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
             The "parent_field" should be specified.',
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
