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
use Ynlo\GraphQLBundle\Type\CollectionComparisonOperatorType;

/**
 * @GraphQL\InputObjectType(description="Create array comparison expression to filter records by values in array.")
 */
class ArrayComparisonExpression
{
    /**
     * @var string|null
     *
     * @GraphQL\Field(type="CollectionComparisonOperator", description="Comparison operator, default value: `IN`")
     */
    private $op = CollectionComparisonOperatorType::IN;

    /**
     * @var array
     *
     * @GraphQL\Field(type="[string]!", description="values to search")
     */
    private $values = [];

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
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }
}
