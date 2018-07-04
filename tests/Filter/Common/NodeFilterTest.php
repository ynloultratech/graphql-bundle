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
use Ynlo\GraphQLBundle\Filter\Common\NodeFilter;
use Ynlo\GraphQLBundle\Model\Filter\NodeComparisonExpression;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;

class NodeFilterTest extends AbstractFilterTest
{
    /**
     * @var NodeComparisonExpression
     */
    protected $condition;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->condition = new NodeComparisonExpression();
        $this->condition->setNodes([new Post(1), new Post(2)]);
    }

    public function testInvalidCondition()
    {
        self::expectExceptionMessage('Invalid filter condition');
        (new NodeFilter())($this->context, $this->qb, false);
    }

    public function testInvalidColumn()
    {
        $metadata = \Mockery::mock(ClassMetadataInfo::class);
        $metadata->expects('hasAssociation')->andReturn(false);
        $this->em->expects('getClassMetadata')->andReturn($metadata);
        self::expectExceptionMessage('There are not valid association in Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post called fieldName.');

        (new NodeFilter())($this->context, $this->qb, $this->condition);
    }

    public function testInvalidField()
    {
        $this->context->setField(null);
        self::expectExceptionMessage('There are not valid field related to this filter.');
        (new NodeFilter())($this->context, $this->qb, $this->condition);
    }

    public function testManyToOneINFilter()
    {
        $metadata = \Mockery::mock(ClassMetadataInfo::class);
        $metadata->expects('hasAssociation')->with('fieldName')->andReturn(true);
        $metadata->expects('getAssociationMapping')->with('fieldName')->andReturn(['type' => ClassMetadataInfo::MANY_TO_ONE]);

        $this->em->expects('getClassMetadata')->andReturn($metadata);

        (new NodeFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName IN(1, 2)', $this->qb->getDQL());
    }

    public function testManyToOneNINFilter()
    {
        $metadata = \Mockery::mock(ClassMetadataInfo::class);
        $metadata->expects('hasAssociation')->with('fieldName')->andReturn(true);
        $metadata->expects('getAssociationMapping')->with('fieldName')->andReturn(['type' => ClassMetadataInfo::MANY_TO_ONE]);

        $this->em->expects('getClassMetadata')->andReturn($metadata);

        $this->condition->setOp(NodeComparisonOperatorType::NIN);
        (new NodeFilter())($this->context, $this->qb, $this->condition);
        self::assertEquals('SELECT p FROM Post p WHERE p.fieldName NOT IN(1, 2)', $this->qb->getDQL());
    }

    public function testManyToManyINFilter()
    {
        $metadata = \Mockery::mock(ClassMetadataInfo::class);
        $metadata->expects('hasAssociation')->with('fieldName')->andReturn(true);
        $metadata->expects('getAssociationMapping')->with('fieldName')->andReturn(['type' => ClassMetadataInfo::MANY_TO_MANY]);

        $this->em->expects('getClassMetadata')->andReturn($metadata);

        (new NodeFilter())($this->context, $this->qb, $this->condition);
        self::assertRegExp('/SELECT p FROM Post p WHERE :fieldName_ids_\d+ MEMBER OF p.fieldName/', $this->qb->getDQL());
    }

    public function testManyToManyNINFilter()
    {
        $metadata = \Mockery::mock(ClassMetadataInfo::class);
        $metadata->expects('hasAssociation')->with('fieldName')->andReturn(true);
        $metadata->expects('getAssociationMapping')->with('fieldName')->andReturn(['type' => ClassMetadataInfo::MANY_TO_MANY]);

        $this->em->expects('getClassMetadata')->andReturn($metadata);

        $this->condition->setOp(NodeComparisonOperatorType::NIN);
        (new NodeFilter())($this->context, $this->qb, $this->condition);
        self::assertRegExp('/SELECT p FROM Post p WHERE :fieldName_ids_\d+ NOT MEMBER OF p.fieldName/', $this->qb->getDQL());
    }
}
