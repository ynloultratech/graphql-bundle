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
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Resolver\AbstractResolver;

/**
 * Resolver to fetch a simple list of nodes without pagination, edges etc
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

        $this->initialize();
        $qb = $this->createQuery();
        $this->applyOrderBy($qb, $orderBy);

        $this->configureQuery($qb);
        foreach ($this->extensions as $extension) {
            $extension->configureQuery($qb, $this, $this->context);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * initialize
     */
    public function initialize()
    {
        $this->queryDefinition = $this->context->getDefinition();
        $this->entity = $this->context->getNode()->getClass();
        $this->objectDefinition = $this->context->getNode();
    }

    /**
     * @param QueryBuilder $qb
     */
    public function configureQuery(QueryBuilder $qb)
    {
        //implements on childs to customize the query
    }

    /**
     * @param QueryBuilder    $qb
     * @param array|OrderBy[] $orderBy
     *
     * @throws Error
     */
    protected function applyOrderBy(QueryBuilder $qb, $orderBy)
    {
        foreach ($orderBy as $order) {
            $column = $order->getField();
            if (strpos($column, '.') !== false) {
                [$relation, $column] = explode('.', $column);
                $childAlias = 'orderBy'.ucfirst($relation);
                $qb->leftJoin("{$this->queryAlias}.{$relation}", $childAlias);
                $qb->addOrderBy("{$this->queryAlias}.$column", $order->getDirection());
            } else {
                if ($this->objectDefinition && $this->objectDefinition->hasField($column)) {
                    $column = $this->objectDefinition->getField($column)->getOriginName() ?: $column;
                }
                $qb->addOrderBy("{$this->queryAlias}.$column", $order->getDirection());
            }
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
