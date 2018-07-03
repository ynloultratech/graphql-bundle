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
 *     description="Create float comparison expression to compare values.

#### Example:

To select values greater than or equal to 10.00
````
op: GTE
value: 10.00
````

or range of values
````
op: BETWEEN
value: 10.00
maxValue: 20.00
````
")
 */
class FloatComparisonExpression
{
    /**
     * @var string|null
     *
     * @GraphQL\Field(type="NumberComparisonOperator!", description="Comparison operator")
     */
    private $op;

    /**
     * @var float|null
     *
     * @GraphQL\Field(type="float!", description="Number value to compare")
     */
    private $value;

    /**
     * @var float|null
     *
     * @GraphQL\Field(type="float", description="Max value to compare when use `BETWEEN`")
     */
    private $maxValue;

    /**
     * @return null|string
     */
    public function getOp(): ?string
    {
        return $this->op;
    }

    /**
     * @param null|string $op
     */
    public function setOp(?string $op): void
    {
        $this->op = $op;
    }

    /**
     * @return null|float
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    /**
     * @param null|float $value
     */
    public function setValue(?float $value): void
    {
        $this->value = $value;
    }

    /**
     * @return float|null
     */
    public function getMaxValue(): ?float
    {
        return $this->maxValue;
    }

    /**
     * @param float|null $maxValue
     */
    public function setMaxValue(?float $maxValue): void
    {
        $this->maxValue = $maxValue;
    }
}
