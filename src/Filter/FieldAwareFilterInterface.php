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

use Ynlo\GraphQLBundle\Definition\FieldDefinition;

/**
 * If filter is directly related to some field/column in the ORM
 * the filter must implement this interface and the field related is present in the filter context
 */
interface FieldAwareFilterInterface extends FilterInterface
{
    /**
     * @return FieldDefinition
     */
    public function getField(): FieldDefinition;

    /**
     * @param FieldDefinition $field
     */
    public function setField(FieldDefinition $field);
}
