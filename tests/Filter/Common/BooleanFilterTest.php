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

use Ynlo\GraphQLBundle\Filter\Common\BooleanFilter;

class BooleanFilterTest extends AbstractFilterTest
{
    public function testInvalidCondition()
    {
        self::expectExceptionMessage('Invalid filter condition');
        (new BooleanFilter())($this->context, $this->qb, []);
    }

    public function testInvalidField()
    {
        $this->context->setField(null);
        self::expectExceptionMessage('There are not valid field related to this filter.');
        (new BooleanFilter())($this->context, $this->qb, true);
    }

    public function testFilterTrue()
    {
        (new BooleanFilter())($this->context, $this->qb, true);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName = 1', $this->qb->getDQL());
    }

    public function testFilterFalse()
    {
        (new BooleanFilter())($this->context, $this->qb, false);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName = 0', $this->qb->getDQL());
    }
}
