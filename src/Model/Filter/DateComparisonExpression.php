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
 */
class DateComparisonExpression
{
    /**
     * @var string|null
     *
     * @GraphQL\Field(type="DateComparisonOperator!", description="Comparison operator")
     */
    private $op;

    /**
     * @var bool
     *
     * @GraphQL\Field(type="bool", description="If strict mode is enabled the value given in not included.")
     */
    private $strict = false;

    /**
     * @var \DateTime|null
     *
     * @GraphQL\Field(type="date!", description="Base date to compare")
     */
    private $date;

    /**
     * @var \DateTime|null
     *
     * @GraphQL\Field(type="date", description="Max value when use **BETWEEN** operator")
     */
    private $maxDate;

    /**
     * @return string|null
     */
    public function getOp(): ?string
    {
        return $this->op;
    }

    /**
     * @param string $op
     *
     * @return DateComparisonExpression
     */
    public function setOp(string $op): DateComparisonExpression
    {
        $this->op = $op;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * @param bool $strict
     */
    public function setStrict(bool $strict): void
    {
        $this->strict = $strict;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     *
     * @return DateComparisonExpression
     */
    public function setDate(?\DateTime $date): DateComparisonExpression
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getMaxDate(): ?\DateTime
    {
        return $this->maxDate;
    }

    /**
     * @param \DateTime|null $maxDate
     *
     * @return DateComparisonExpression
     */
    public function setMaxDate(?\DateTime $maxDate): DateComparisonExpression
    {
        $this->maxDate = $maxDate;

        return $this;
    }
}
