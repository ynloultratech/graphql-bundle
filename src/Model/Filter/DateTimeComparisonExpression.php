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
 * @GraphQL\InputObjectType(description="Create date comparison expression to filter values by date.")
 * @GraphQL\OverrideField(name="date", type="datetime!")
 * @GraphQL\OverrideField(name="maxDate", type="datetime")
 */
class DateTimeComparisonExpression extends DateComparisonExpression
{
}
