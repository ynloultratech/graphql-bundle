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

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Filter\FilterInterface;
use Ynlo\GraphQLBundle\Model\Filter\NodeComparisonExpression;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;

/**
 * Node filter to filter by related nodes
 */
class NodeFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(FilterContext $context, QueryBuilder $qb, $condition)
    {
        if (!$condition instanceof NodeComparisonExpression) {
            throw new \RuntimeException('Invalid filter condition');
        }

        if (!$context->getField() || !$context->getField()->getName()) {
            throw new \RuntimeException('There are not valid field related to this filter.');
        }

        $entity = $context->getNode()->getClass();
        $metadata = $qb->getEntityManager()->getClassMetadata($entity);
        $column = $context->getField()->getOriginName();

        if (!$metadata->hasAssociation($column)) {
            throw new \RuntimeException(sprintf('There are not valid association in %s called %s.', $entity, $column));
        }

        $association = $metadata->getAssociationMapping($column);

        $ids = [];
        foreach ($condition->getNodes() as $node) {
            if ($node instanceof NodeInterface) {
                $ids[] = $node->getId();
            }
        }
        $ids = array_filter($ids);

        $alias = $qb->getRootAliases()[0];

        switch ($association['type']) {
            case ClassMetadataInfo::MANY_TO_ONE:
            case ClassMetadataInfo::ONE_TO_ONE:
            case ClassMetadataInfo::ONE_TO_MANY:
                if ($condition->getOp() === NodeComparisonOperatorType::IN) {
                    $qb->andWhere($qb->expr()->in("{$alias}.{$column}", $ids));
                } else {
                    $qb->andWhere($qb->expr()->notIn("{$alias}.{$column}", $ids));
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
}
