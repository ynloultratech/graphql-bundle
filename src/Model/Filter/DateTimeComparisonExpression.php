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
 * @GraphQL\InputObjectType(
 *     description="Create date comparison expression to filter values by date.

#### Example:

To select values with date after January 15, 2018:
````
op: AFTER
strict: true
date: '2018-01-15T18:05:00-00:00'
````

or values using date range to select all records in January, 2018:
````
op: BETWEEN
date: '2018-01-01T18:05:00-00:00'
maxDate: '2018-01-31T18:05:00-00:00'
````
")
 * @GraphQL\OverrideField(name="date", type="datetime!")
 * @GraphQL\OverrideField(name="maxDate", type="datetime")
 */
class DateTimeComparisonExpression extends DateComparisonExpression
{
}
