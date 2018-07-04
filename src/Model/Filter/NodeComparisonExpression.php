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
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;

/**
 * @GraphQL\InputObjectType(
 *     description="Create Node comparison expression to filter values by related nodes.

#### Example:

Include all values with given ids
````
value: ['Q2nk6MQ==', 'Q2cnk6Mg==']
````

or exclude values with given ids
````
op: NIN
value: ['Q2nk6MQ==', 'Q2cnk6Mg==']
````
")
 */
class NodeComparisonExpression
{
    /**
     * @var string|null
     *
     * @GraphQL\Field(type="NodeComparisonOperator", description="Comparison operator, default `IN`")
     */
    private $op = NodeComparisonOperatorType::IN;

    /**
     * @var NodeInterface[]
     *
     * @GraphQL\Field(type="[ID!]!", description="Array of nodes to search")
     */
    private $nodes = [];

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
     * @return NodeInterface[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @param NodeInterface[] $nodes
     */
    public function setNodes(array $nodes): void
    {
        $this->nodes = $nodes;
    }
}
