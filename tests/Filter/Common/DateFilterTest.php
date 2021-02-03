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

use Ynlo\GraphQLBundle\Filter\Common\DateFilter;
use Ynlo\GraphQLBundle\Model\Filter\DateComparisonExpression;
use Ynlo\GraphQLBundle\Type\DateComparisonOperatorType;

class DateFilterTest extends AbstractFilterTest
{
    /**
     * @var DateComparisonExpression
     */
    protected $condition;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->condition = new DateComparisonExpression();
        $this->condition->setDate(\DateTime::createFromFormat('Y/m/d H:i:s', '2018/01/01 12:30:00'));
    }

    public function testInvalidCondition()
    {
        self::expectExceptionMessage('Invalid filter condition');
        (new DateFilter())($this->context, $this->qb, []);
    }

    public function testInvalidField()
    {
        $this->context->setField(null);
        self::expectExceptionMessage('There are not valid field related to this filter.');
        (new DateFilter())($this->context, $this->qb, $this->condition);
    }

    public function testDefaultAfter()
    {
        $this->condition->setOp(DateComparisonOperatorType::AFTER);
        (new DateFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName >= \'2018-01-01 12:30:00\'', $this->qb->getDQL());
    }

    public function testDefaultAfterStrict()
    {
        $this->condition->setOp(DateComparisonOperatorType::AFTER);
        $this->condition->setStrict(true);
        (new DateFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName > \'2018-01-01 12:30:00\'', $this->qb->getDQL());
    }

    public function testDefaultBefore()
    {
        $this->condition->setOp(DateComparisonOperatorType::BEFORE);
        (new DateFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName <= \'2018-01-01 12:30:00\'', $this->qb->getDQL());
    }

    public function testDefaultBeforeStrict()
    {
        $this->condition->setOp(DateComparisonOperatorType::BEFORE);
        $this->condition->setStrict(true);
        (new DateFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName < \'2018-01-01 12:30:00\'', $this->qb->getDQL());
    }

    public function testDefaultBetween()
    {
        $this->condition->setOp(DateComparisonOperatorType::BETWEEN);
        $this->condition->setMaxDate(\DateTime::createFromFormat('Y/m/d H:i:s', '2018/01/31 12:30:00'));
        (new DateFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName >= \'2018-01-01 12:30:00\' AND p.fieldName <= \'2018-01-31 12:30:00\'', $this->qb->getDQL());
    }

    public function testDefaultBetweenStrict()
    {
        $this->condition->setOp(DateComparisonOperatorType::BETWEEN);
        $this->condition->setStrict(true);
        $this->condition->setMaxDate(\DateTime::createFromFormat('Y/m/d H:i:s', '2018/01/31 12:30:00'));
        (new DateFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName > \'2018-01-01 12:30:00\' AND p.fieldName < \'2018-01-31 12:30:00\'', $this->qb->getDQL());
    }
}
