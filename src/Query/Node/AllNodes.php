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
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Pagination\DoctrineOffsetCursorPaginator;
use Ynlo\GraphQLBundle\Pagination\PaginationRequest;
use Ynlo\GraphQLBundle\Resolver\AbstractResolver;

/**
 * Base class to fetch nodes
 */
class AllNodes extends AbstractResolver
{
    /**
     * @var string
     */
    protected $queryAlias = 'o';

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var QueryDefinition
     */
    protected $queryDefinition;

    /**
     * @var ObjectDefinition
     */
    protected $objectDefinition;

    /**
     * @param NodeInterface|null $root
     * @param int|null           $first
     * @param int|null           $last
     * @param int|null           $after
     * @param int|null           $before
     * @param OrderBy[]          $orderBy
     *
     * @return mixed
     *
     * @throws Error
     */
    public function __invoke(NodeInterface $root = null, $first = null, $last = null, $after = null, $before = null, $orderBy = [])
    {
        if ($this->context->getDefinition()->hasMeta('node')) {
            $objectType = $this->context->getDefinition()->getMeta('node');
        } else {
            $objectType = $this->context->getDefinition()->getType();
        }

        $this->queryDefinition = $this->context->getDefinition();
        $this->objectDefinition = $this->context->getDefinitionManager()->getType($objectType);
        $this->entity = $this->context->getDefinitionManager()->getType($objectType)->getClass();

        $qb = $this->createQuery();
        $this->applyOrderBy($qb, $orderBy);

        if ($root) {
            $this->applyFilterByParent($qb, $root);
        }

        $this->modifyQuery($qb);

        if (!$first && !$last) {
            $error = sprintf('You must provide a `first` or `last` value to properly paginate records in "%s" connection.', $this->queryDefinition->getName());
            throw new Error($error);
        }

        if ($this->queryDefinition->hasMeta('connection_limit')) {
            $limitAllowed = $this->queryDefinition->getMeta('connection_limit');
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

        $paginator = new DoctrineOffsetCursorPaginator();

        return $paginator->paginate($qb, new PaginationRequest($first, $last, $after, $before));
    }

    /**
     * @param QueryBuilder $qb
     */
    public function modifyQuery(QueryBuilder $qb)
    {
        //implements on childs to customize the query
    }

    /**
     * @param QueryBuilder  $qb
     * @param NodeInterface $root
     */
    protected function applyFilterByParent(QueryBuilder $qb, NodeInterface $root)
    {
        $parentField = null;
        if ($this->queryDefinition->hasMeta('connection_parent_field')) {
            $parentField = $this->queryDefinition->getMeta('connection_parent_field');
        }
        if (!$parentField) {
            throw new \RuntimeException(sprintf('Missing parent field to filter "%s" by given parent. The parentField should be specified in the connection.', $this->queryDefinition->getName()));
        }

        if ($this->objectDefinition->hasField($parentField)) {
            $parentField = $this->objectDefinition->getField($parentField)->getOriginName();
        }

        $paramName = 'root'.mt_rand();
        $qb->andWhere(sprintf('%s.%s = :%s', $this->queryAlias, $parentField, $paramName))
           ->setParameter($paramName, $root);
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $orderBy
     *
     * @throws Error
     */
    protected function applyOrderBy(QueryBuilder $qb, $orderBy)
    {
        $refClass = new \ReflectionClass($this->entity);
        //TODO: allow sort using nested entities, e.g. profile.username
        foreach ($orderBy as $order) {
            $order->getField();
            if ($this->objectDefinition->hasField($order->getField())) {
                $fieldDefinition = $this->objectDefinition->getField($order->getField());
                if ($fieldDefinition->getOriginType() === \ReflectionProperty::class) {
                    if ($refClass->hasProperty($fieldDefinition->getOriginName())) {
                        $qb->addOrderBy($this->queryAlias.'.'.$fieldDefinition->getOriginName(), $order->getDirection());

                        continue;
                    }
                }
            }

            throw new Error(sprintf('The field "%s" its not valid to order in "%s" connection', $order->getField(), $this->queryDefinition->getName()));
        }
    }

    /**
     * @return QueryBuilder
     */
    protected function createQuery(): QueryBuilder
    {
        return $this->getManager()
                    ->getRepository($this->entity)
                    ->createQueryBuilder($this->queryAlias);
    }
}
