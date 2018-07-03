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
 *     description="Create enum comparison expression to filter records by current values.

#### Example:

Include all records with given values
````
value: ['{VALUE_1}', '{VALUE_2}']
````
")
 */
class EnumComparisonExpression extends ArrayComparisonExpression
{

}
