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

use Ynlo\GraphQLBundle\Filter\Common\StringFilter;
use Ynlo\GraphQLBundle\Model\Filter\StringComparisonExpression;
use Ynlo\GraphQLBundle\Type\StringComparisonOperatorType;

class StringFilterTest extends AbstractFilterTest
{
    /**
     * @var StringComparisonExpression
     */
    protected $condition;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->condition = new StringComparisonExpression();
        $this->condition->setValue('Lorem');
    }

    public function testInvalidCondition()
    {
        self::expectExceptionMessage('Invalid filter condition');
        (new StringFilter())($this->context, $this->qb, []);
    }

    public function testInvalidField()
    {
        $this->context->setField(null);
        self::expectExceptionMessage('There are not valid field related to this filter.');
        (new StringFilter())($this->context, $this->qb, $this->condition);
    }

    public function testDefaultFilter()
    {
        (new StringFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName LIKE \'%Lorem%\'', $this->qb->getDQL());
    }

    public function testEqualFilter()
    {
        $this->condition->setOp(StringComparisonOperatorType::EQUAL);
        (new StringFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName = \'Lorem\'', $this->qb->getDQL());
    }

    public function testContainsFilter()
    {
        $this->condition->setOp(StringComparisonOperatorType::CONTAINS);
        (new StringFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName LIKE \'%Lorem%\'', $this->qb->getDQL());
    }

    public function testStartWithFilter()
    {
        $this->condition->setOp(StringComparisonOperatorType::STARTS_WITH);
        (new StringFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName LIKE \'Lorem%\'', $this->qb->getDQL());
    }

    public function testEndWithFilter()
    {
        $this->condition->setOp(StringComparisonOperatorType::ENDS_WITH);
        (new StringFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName LIKE \'%Lorem\'', $this->qb->getDQL());
    }
}
