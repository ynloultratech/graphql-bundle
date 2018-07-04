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

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ynlo\GraphQLBundle\Filter\Common\ArrayFilter;
use Ynlo\GraphQLBundle\Model\Filter\ArrayComparisonExpression;

class ArrayFilterTest extends AbstractFilterTest
{
    /**
     * @var ArrayComparisonExpression
     */
    protected $condition;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->condition = new ArrayComparisonExpression();
        $this->condition->setValues(['VALUE1', 'VALUE2']);
    }

    public function testInvalidCondition()
    {
        self::expectExceptionMessage('Invalid filter condition');
        (new ArrayFilter())($this->context, $this->qb, []);
    }

    public function testInvalidField()
    {
        $this->context->setField(null);
        self::expectExceptionMessage('There are not valid field related to this filter.');
        (new ArrayFilter())($this->context, $this->qb, $this->condition);
    }

    public function testInvalidColumn()
    {
        $metadata = \Mockery::mock(ClassMetadataInfo::class);
        $metadata->expects('hasField')->andReturn(false);
        $this->em->expects('getClassMetadata')->andReturn($metadata);
        self::expectExceptionMessage('There are not valid column in Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post called fieldName.');

        (new ArrayFilter())($this->context, $this->qb, $this->condition);
    }

    public function testArrayFilter()
    {
        $metadata = \Mockery::mock(ClassMetadataInfo::class);
        $metadata->expects('hasField')->with('fieldName')->andReturn(true);
        $metadata->expects('getFieldMapping')->with('fieldName')->andReturn(['type' => 'array']);

        $this->em->expects('getClassMetadata')->andReturn($metadata);

        (new ArrayFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName LIKE \'%"VALUE1"%\' AND p.fieldName LIKE \'%"VALUE2"%\'', $this->qb->getDQL());
    }

    public function testSimpleArrayFilter()
    {
        $metadata = \Mockery::mock(ClassMetadataInfo::class);
        $metadata->expects('hasField')->with('fieldName')->andReturn(true);
        $metadata->expects('getFieldMapping')->with('fieldName')->andReturn(['type' => 'simple_array']);

        $this->em->expects('getClassMetadata')->andReturn($metadata);

        (new ArrayFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE (p.fieldName = \'%VALUE1%\' OR p.fieldName LIKE \'VALUE1,%\' OR p.fieldName LIKE \'%,VALUE1,%\' OR p.fieldName LIKE \'%,VALUE1\') AND (p.fieldName = \'%VALUE2%\' OR p.fieldName LIKE \'VALUE2,%\' OR p.fieldName LIKE \'%,VALUE2,%\' OR p.fieldName LIKE \'%,VALUE2\')', $this->qb->getDQL());
    }
}
