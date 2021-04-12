<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter\Common;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\HasChild;
use Elastica\Query\QueryString;
use Elastica\Query\Term;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Filter\FilterInterface;
use Ynlo\GraphQLBundle\Model\Filter\EnumComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\NodeComparisonExpression;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;

/**
 * Node filter to filter by related nodes
 */
class NodeFilter implements FilterInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * NodeFilter constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, $qb, $condition)
    {
        if (!$condition instanceof NodeComparisonExpression) {
            throw new \RuntimeException('Invalid filter condition');
        }

        if (!$context->getField() || !$context->getField()->getName()) {
            throw new \RuntimeException('There are not valid field related to this filter.');
        }

        $entity = $context->getNode()->getClass();
        $metadata = $this->em->getClassMetadata($entity);
        $column = $context->getField()->getOriginName();
        if (!$column || $context->getField()->getOriginType() === 'ReflectionMethod') {
            $column = $context->getField()->getName();
        }

        if (!$metadata->hasAssociation($column)) {
            throw new \RuntimeException(sprintf('There are not valid association in %s called %s.', $entity, $column));
        }

        $association = $metadata->getAssociationMapping($column);

        if ($qb instanceof QueryBuilder) {
            $alias = $qb->getRootAliases()[0];
            $this->applyFilter($qb, $association['type'], $alias, $column, $condition);
        } else {
            $this->applyElasticFilter($qb, $association['type'], $column, $condition);
        }
    }

    /**
     * @param QueryBuilder             $qb
     * @param string                   $assocType
     * @param string                   $alias
     * @param string                   $column
     * @param NodeComparisonExpression $condition
     */
    protected function applyFilter(QueryBuilder $qb, $assocType, $alias, $column, NodeComparisonExpression $condition): void
    {
        $ids = [];
        foreach ($condition->getNodes() as $node) {
            if ($node instanceof NodeInterface) {
                $ids[] = $node->getId();
            }
        }
        $ids = array_filter($ids);

        switch ($assocType) {
            case ClassMetadataInfo::MANY_TO_ONE:
            case ClassMetadataInfo::ONE_TO_ONE:
            case ClassMetadataInfo::ONE_TO_MANY:
                if ($condition->getOp() === NodeComparisonOperatorType::IN) {
                    if (empty($ids)) {
                        $qb->andWhere($qb->expr()->isNull("{$alias}.{$column}"));
                    } else {
                        $qb->andWhere($qb->expr()->in("{$alias}.{$column}", $ids));
                    }
                } else {
                    if (empty($ids)) {
                        $qb->andWhere($qb->expr()->isNotNull("{$alias}.{$column}"));
                    } else {
                        $qb->andWhere($qb->expr()->notIn("{$alias}.{$column}", $ids));
                    }
                }
                break;
            case ClassMetadataInfo::MANY_TO_MANY:
                $paramName = sprintf('%s_ids_%s', $column, mt_rand());
                if ($condition->getOp() === NodeComparisonOperatorType::IN) {
                    $qb->andWhere(sprintf(':%s MEMBER OF %s.%s', $paramName, $alias, $column))
                       ->setParameter($paramName, $ids);
                } else {
                    $qb->andWhere(sprintf(':%s NOT MEMBER OF %s.%s', $paramName, $alias, $column))
                       ->setParameter($paramName, $ids);
                }
                break;
        }
    }

    /**
     * @param BoolQuery                $qb
     * @param string                   $assocType
     * @param string                   $column
     * @param NodeComparisonExpression $condition
     */
    protected function applyElasticFilter(BoolQuery $qb, $assocType, $column, NodeComparisonExpression $condition): void
    {
        $ids = [];
        foreach ($condition->getNodes() as $node) {
            if ($node instanceof NodeInterface) {
                $ids[] = $node->getId();
            }
        }
        $ids = array_filter($ids);
        dump($column);
        switch ($assocType) {
            case ClassMetadataInfo::MANY_TO_ONE:
            case ClassMetadataInfo::ONE_TO_ONE:
            case ClassMetadataInfo::ONE_TO_MANY:
                if ($condition->getOp() === NodeComparisonOperatorType::IN) {
                    if (empty($ids)) {
                        $columnQuery = new Exists($column);
                        $qb->addMustNot($columnQuery);
                    } else {
                        $bool = new BoolQuery();
                        foreach ($ids as $id) {
                            $columnQuery = new Term();
                            $columnQuery->setTerm(sprintf('%s.id', $column), $id);
                            $bool->addShould($columnQuery);
                        }
                        $qb->addMust($bool);
                    }
                } else {
                    if (empty($ids)) {
                        $columnQuery = new Exists($column);
                        $qb->addMust($columnQuery);
                    } else {
                        $bool = new BoolQuery();
                        foreach ($ids as $id) {
                            $columnQuery = new Term();
                            $columnQuery->setTerm(sprintf('%s.id', $column), $id);
                            $bool->addMustNot($columnQuery);
                        }
                        $qb->addMust($bool);
                    }
                }
                break;
            case ClassMetadataInfo::MANY_TO_MANY:
                // TODO?
                break;
        }
    }
}
