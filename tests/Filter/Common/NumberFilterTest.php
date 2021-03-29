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

use Ynlo\GraphQLBundle\Filter\Common\NumberFilter;
use Ynlo\GraphQLBundle\Model\Filter\IntegerComparisonExpression;
use Ynlo\GraphQLBundle\Type\NumberComparisonOperatorType;

class NumberFilterTest extends AbstractFilterTest
{
    /**
     * @var IntegerComparisonExpression
     */
    protected $condition;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->condition = new IntegerComparisonExpression();
        $this->condition->setValue(5);
    }

    public function testInvalidCondition()
    {
        self::expectExceptionMessage('Invalid filter condition');
        (new NumberFilter())($this->context, $this->qb, []);
    }

    public function testInvalidField()
    {
        $this->context->setField(null);
        self::expectExceptionMessage('There are not valid field related to this filter.');
        (new NumberFilter())($this->context, $this->qb, $this->condition);
    }

    public function testEQtFilter()
    {
        $this->condition->setOp(NumberComparisonOperatorType::EQ);
        (new NumberFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName = 5', $this->qb->getDQL());
    }

    public function testNEQFilter()
    {
        $this->condition->setOp(NumberComparisonOperatorType::NEQ);
        (new NumberFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName <> 5', $this->qb->getDQL());
    }

    public function testGTFilter()
    {
        $this->condition->setOp(NumberComparisonOperatorType::GT);
        (new NumberFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName > 5', $this->qb->getDQL());
    }

    public function testGTEFilter()
    {
        $this->condition->setOp(NumberComparisonOperatorType::GTE);
        (new NumberFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName >= 5', $this->qb->getDQL());
    }

    public function testLTFilter()
    {
        $this->condition->setOp(NumberComparisonOperatorType::LT);
        (new NumberFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName < 5', $this->qb->getDQL());
    }

    public function testLTEFilter()
    {
        $this->condition->setOp(NumberComparisonOperatorType::LTE);
        (new NumberFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName <= 5', $this->qb->getDQL());
    }

    public function testBETWEENFilter()
    {
        $this->condition->setOp(NumberComparisonOperatorType::BETWEEN);
        $this->condition->setMaxValue(10);
        (new NumberFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName BETWEEN 5 AND 10', $this->qb->getDQL());
    }
}
