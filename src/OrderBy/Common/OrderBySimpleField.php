<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\OrderBy\Common;

use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\OrderBy\OrderByContext;
use Ynlo\GraphQLBundle\OrderBy\OrderByInterface;
use Ynlo\GraphQLBundle\Util\FieldOptionsHelper;

class OrderBySimpleField implements OrderByInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(OrderByContext $context, QueryBuilder $qb, $alias, OrderBy $orderBy)
    {
        $column = $orderBy->getField();

        // use alias
        $orderByFields = FieldOptionsHelper::normalize($context->getParentContext()->getDefinition()->getMeta('pagination')['order_by'] ?? ['*']);
        if (isset($orderByFields[$column])) {
            $column = $orderByFields[$column];
        } else {
            $field = $context->getNode()->getField($column);
            $column = $field->getOriginName();
        }

        $qb->addOrderBy("{$alias}.$column", $orderBy->getDirection());
    }
}
