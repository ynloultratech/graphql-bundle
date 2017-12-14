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

use Doctrine\ORM\QueryBuilder;
use GraphQL\Error\Error;
use Ynlo\GraphQLBundle\Definition\Extension\PaginationDefinitionExtension;
use Ynlo\GraphQLBundle\Extension\ExtensionManager;
use Ynlo\GraphQLBundle\Model\ConnectionInterface;
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

        $this->initialize();

        $qb = $this->createQuery();
        $this->applyOrderBy($qb, $orderBy);

        if ($this->getContext()->getRoot()) {
            $this->applyFilterByParent($qb, $this->getContext()->getRoot());
        }

        $this->configureQuery($qb);
        foreach ($this->container->get(ExtensionManager::class)->getExtensions() as $extension) {
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

    /**
     * @return ConnectionInterface
     */
    protected function createConnection(): ConnectionInterface
    {
        return new NodeConnection();
    }

    /**
     * @return DoctrineCursorPaginatorInterface
     */
    protected function createPaginator(): DoctrineCursorPaginatorInterface
    {
        return new DoctrineOffsetCursorPaginator();
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
             The "parent_field" should be specified.',
                    $this->queryDefinition->getName()
                )
            );
        }

        if ($this->objectDefinition->hasField($parentField)) {
            $parentField = $this->objectDefinition->getField($parentField)->getOriginName();
        }

        $paramName = 'root'.mt_rand();
        if ($this->queryDefinition->getMeta('pagination')['parent_relation'] === PaginationDefinitionExtension::MANY_TO_MANY) {
            $qb->andWhere(sprintf(':%s MEMBER OF %s.%s', $paramName, $this->queryAlias, $parentField))
               ->setParameter($paramName, $root);
        } else {
            $qb->andWhere(sprintf('%s.%s = :%s', $this->queryAlias, $parentField, $paramName))
               ->setParameter($paramName, $root);
        }
    }
}
