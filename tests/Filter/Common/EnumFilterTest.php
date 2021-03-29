<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Filter\Common;

use Ynlo\GraphQLBundle\Filter\Common\EnumFilter;
use Ynlo\GraphQLBundle\Model\Filter\EnumComparisonExpression;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;

class EnumFilterTest extends AbstractFilterTest
{
    /**
     * @var EnumComparisonExpression
     */
    protected $condition;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->condition = new EnumComparisonExpression();
        $this->condition->setValues(['VALUE1', 'VALUE2']);
    }

    public function testInvalidCondition()
    {
        self::expectExceptionMessage('Invalid filter condition');
        (new EnumFilter())($this->context, $this->qb, false);
    }

    public function testInvalidField()
    {
        $this->context->setField(null);
        self::expectExceptionMessage('There are not valid field related to this filter.');
        (new EnumFilter())($this->context, $this->qb, $this->condition);
    }

    public function testDefaultFilter()
    {
        (new EnumFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName IN(\'VALUE1\', \'VALUE2\')', $this->qb->getDQL());
    }

    public function testNINFilter()
    {
        $this->condition->setOp(NodeComparisonOperatorType::NIN);
        (new EnumFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName NOT IN(\'VALUE1\', \'VALUE2\')', $this->qb->getDQL());
    }
}
