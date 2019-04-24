<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Model\Filter;

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @GraphQL\InputObjectType(description="Create integer comparison expression to compare values.")
 * @GraphQL\OverrideField(name="value", type="int!")
 * @GraphQL\OverrideField(name="maxValue", type="int")
 */
class IntegerComparisonExpression extends FloatComparisonExpression
{

}
