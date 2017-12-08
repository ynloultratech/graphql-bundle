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
use Ynlo\GraphQLBundle\Action\AbstractNodeAction;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Model\OrderBy;

/**
 * Base class to fetch nodes
 */
class AllNodes extends AbstractNodeAction
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
     * @var ObjectDefinition
     */
    protected $objectDefinition;

    /**
     * @param int|null  $first
     * @param int|null  $last
     * @param OrderBy[] $orderBy
     *
     * @return mixed
     */
    public function __invoke($first = null, $last = null, $orderBy = [])
    {
        $objectType = $this->context->getDefinition()->getType();
        $this->objectDefinition = $this->context->getDefinitionManager()->getType($objectType);
        $this->entity = $this->context->getDefinitionManager()->getType($objectType)->getClass();

        $qb = $this->createQuery();
        $this->applyOrderBy($qb, $orderBy);

        if ($first) {
            $qb->setMaxResults(abs($first));
        } elseif ($last) {
            $qb->setMaxResults(abs($last));

            //invert all orders
            /** @var \Doctrine\ORM\Query\Expr\OrderBy[] $orders */
            $orders = $qb->getDQLPart('orderBy');
            $qb->resetDQLPart('orderBy');
            foreach ($orders as &$order) {
                foreach ($order->getParts() as &$part) {
                    if (strpos($part, 'ASC')) {
                        $part = str_replace('ASC', '', $part);
                        $qb->addOrderBy($part, 'DESC');
                    } else {
                        $part = str_replace('DESC', '', $part);
                        $qb->addOrderBy($part, 'ASC');
                    }
                }
            }
        }

        return $qb->getQuery()->execute();
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

            throw new Error(sprintf('The field "%s" its not valid to order', $order->getField()));
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
