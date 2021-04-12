<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Pagination;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Ynlo\GraphQLBundle\Model\NodeConnection;
use Ynlo\GraphQLBundle\Pagination\DoctrineCursorPaginator;
use Ynlo\GraphQLBundle\Pagination\PaginationRequest;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;

class DoctrineOffsetCursorPaginatorTest extends MockeryTestCase
{
    public function testPaginateFirst()
    {
        $em = \Mockery::mock(EntityManagerInterface::class);

        /** @var QueryBuilder|Mock $qb */
        $qb = \Mockery::mock(QueryBuilder::class, [$em])->makePartial();

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->expects('getIdentifier')->andReturn(['id']);
        $em->expects('getClassMetadata')->andReturn($classMetadata);

        $countQuery = \Mockery::mock(AbstractQuery::class);
        $countQuery->allows('getSingleScalarResult')->andReturn(5);//total

        $countQuery->allows('execute')->andReturn(
            [
                new Post(1),
                new Post(2),
                new Post(3),
            ]
        );

        $qb->allows('getQuery')->andReturn($countQuery);

        $qb->select('p')
           ->from(Post::class, 'p')
           ->where('p.title = :title');

        $paginator = new DoctrineCursorPaginator($em);
        $connection = new NodeConnection();
        $paginator->paginate($qb, new PaginationRequest(3), $connection);

        self::assertEquals(0, $qb->getFirstResult());
        self::assertEquals(3, $qb->getMaxResults());
        self::assertEquals(5, $connection->getTotalCount());
        self::assertFalse($connection->getPageInfo()->isHasPreviousPage());
        self::assertTrue($connection->getPageInfo()->isHasNextPage());
        self::assertEquals($connection->getEdges()[0]->getCursor(), $connection->getPageInfo()->getStartCursor());
        self::assertEquals($connection->getEdges()[2]->getCursor(), $connection->getPageInfo()->getEndCursor());

        self::assertEquals(1, $connection->getEdges()[0]->getNode()->getId());
        self::assertEquals(2, $connection->getEdges()[1]->getNode()->getId());
        self::assertEquals(3, $connection->getEdges()[2]->getNode()->getId());

        self::assertEquals(base64_encode('cursor:0'), $connection->getEdges()[0]->getCursor());
        self::assertEquals(base64_encode('cursor:1'), $connection->getEdges()[1]->getCursor());
        self::assertEquals(base64_encode('cursor:2'), $connection->getEdges()[2]->getCursor());
    }

    public function testPaginateFirstWithAfter()
    {
        $em = \Mockery::mock(EntityManagerInterface::class);

        /** @var QueryBuilder|Mock $qb */
        $qb = \Mockery::mock(QueryBuilder::class, [$em])->makePartial();

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->expects('getIdentifier')->andReturn(['id']);
        $em->expects('getClassMetadata')->andReturn($classMetadata);

        $countQuery = \Mockery::mock(AbstractQuery::class);
        $countQuery->allows('getSingleScalarResult')->andReturn(5);

        $countQuery->allows('execute')->andReturn(
            [
                new Post(3),
                new Post(4),
                new Post(5),
            ]
        );

        $qb->allows('getQuery')->andReturn($countQuery);

        $qb->select('p')
           ->from(Post::class, 'p')
           ->where('p.title = :title');

        $paginator = new DoctrineCursorPaginator($em);
        $connection = new NodeConnection();
        $paginator->paginate($qb, new PaginationRequest(3, null, base64_encode('cursor:1')), $connection);

        self::assertEquals(2, $qb->getFirstResult());
        self::assertEquals(3, $qb->getMaxResults());
        self::assertEquals(5, $connection->getTotalCount());
        self::assertTrue($connection->getPageInfo()->isHasPreviousPage());
        self::assertFalse($connection->getPageInfo()->isHasNextPage());
        self::assertEquals($connection->getEdges()[0]->getCursor(), $connection->getPageInfo()->getStartCursor());
        self::assertEquals($connection->getEdges()[2]->getCursor(), $connection->getPageInfo()->getEndCursor());

        self::assertEquals(3, $connection->getEdges()[0]->getNode()->getId());
        self::assertEquals(4, $connection->getEdges()[1]->getNode()->getId());
        self::assertEquals(5, $connection->getEdges()[2]->getNode()->getId());

        self::assertEquals(base64_encode('cursor:2'), $connection->getEdges()[0]->getCursor());
        self::assertEquals(base64_encode('cursor:3'), $connection->getEdges()[1]->getCursor());
        self::assertEquals(base64_encode('cursor:4'), $connection->getEdges()[2]->getCursor());
    }

    public function testPaginateFirstWithBefore()
    {
        $em = \Mockery::mock(EntityManagerInterface::class);

        /** @var QueryBuilder|Mock $qb */
        $qb = \Mockery::mock(QueryBuilder::class, [$em])->makePartial();

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->expects('getIdentifier')->andReturn(['id']);
        $em->expects('getClassMetadata')->andReturn($classMetadata);

        $countQuery = \Mockery::mock(AbstractQuery::class);
        $countQuery->allows('getSingleScalarResult')->andReturn(5);

        $countQuery->allows('execute')->andReturn(
            [
                new Post(1),
                new Post(2),
            ]
        );

        $qb->allows('getQuery')->andReturn($countQuery);

        $qb->select('p')
           ->from(Post::class, 'p')
           ->where('p.title = :title');

        $paginator = new DoctrineCursorPaginator($em);
        $connection = new NodeConnection();
        $paginator->paginate($qb, new PaginationRequest(3, null, null, base64_encode('cursor:2')), $connection);

        self::assertEquals(0, $qb->getFirstResult());
        self::assertEquals(2, $qb->getMaxResults());
        self::assertEquals(5, $connection->getTotalCount());
        self::assertFalse($connection->getPageInfo()->isHasPreviousPage());
        self::assertTrue($connection->getPageInfo()->isHasNextPage());
        self::assertEquals($connection->getEdges()[0]->getCursor(), $connection->getPageInfo()->getStartCursor());
        self::assertEquals($connection->getEdges()[1]->getCursor(), $connection->getPageInfo()->getEndCursor());

        self::assertEquals(1, $connection->getEdges()[0]->getNode()->getId());
        self::assertEquals(2, $connection->getEdges()[1]->getNode()->getId());

        self::assertEquals(base64_encode('cursor:0'), $connection->getEdges()[0]->getCursor());
        self::assertEquals(base64_encode('cursor:1'), $connection->getEdges()[1]->getCursor());
    }

    public function testPaginateLast()
    {
        $em = \Mockery::mock(EntityManagerInterface::class);

        /** @var QueryBuilder|Mock $qb */
        $qb = \Mockery::mock(QueryBuilder::class, [$em])->makePartial();

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->expects('getIdentifier')->andReturn(['id']);
        $em->expects('getClassMetadata')->andReturn($classMetadata);

        $countQuery = \Mockery::mock(AbstractQuery::class);
        $countQuery->allows('getSingleScalarResult')->andReturn(5);

        $countQuery->allows('execute')->andReturn(
            [
                new Post(3),
                new Post(4),
                new Post(5),
            ]
        );

        $qb->allows('getQuery')->andReturn($countQuery);

        $qb->select('p')
           ->from(Post::class, 'p')
           ->where('p.title = :title');

        $paginator = new DoctrineCursorPaginator($em);
        $connection = new NodeConnection();
        $paginator->paginate($qb, new PaginationRequest(null, 3), $connection);

        self::assertEquals(2, $qb->getFirstResult());
        self::assertEquals(3, $qb->getMaxResults());
        self::assertEquals(5, $connection->getTotalCount());
        self::assertTrue($connection->getPageInfo()->isHasPreviousPage());
        self::assertFalse($connection->getPageInfo()->isHasNextPage());
        self::assertEquals($connection->getEdges()[0]->getCursor(), $connection->getPageInfo()->getStartCursor());
        self::assertEquals($connection->getEdges()[2]->getCursor(), $connection->getPageInfo()->getEndCursor());

        self::assertEquals(3, $connection->getEdges()[0]->getNode()->getId());
        self::assertEquals(4, $connection->getEdges()[1]->getNode()->getId());
        self::assertEquals(5, $connection->getEdges()[2]->getNode()->getId());

        self::assertEquals(base64_encode('cursor:2'), $connection->getEdges()[0]->getCursor());
        self::assertEquals(base64_encode('cursor:3'), $connection->getEdges()[1]->getCursor());
        self::assertEquals(base64_encode('cursor:4'), $connection->getEdges()[2]->getCursor());
    }

    public function testPaginateLastWithAfter()
    {
        $em = \Mockery::mock(EntityManagerInterface::class);

        /** @var QueryBuilder|Mock $qb */
        $qb = \Mockery::mock(QueryBuilder::class, [$em])->makePartial();

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->expects('getIdentifier')->andReturn(['id']);
        $em->expects('getClassMetadata')->andReturn($classMetadata);

        $countQuery = \Mockery::mock(AbstractQuery::class);
        $countQuery->allows('getSingleScalarResult')->andReturn(5);

        $countQuery->allows('execute')->andReturn(
            [
                new Post(3),
                new Post(4),
                new Post(5),
            ]
        );

        $qb->allows('getQuery')->andReturn($countQuery);

        $qb->select('p')
           ->from(Post::class, 'p')
           ->where('p.title = :title');

        $paginator = new DoctrineCursorPaginator($em);
        $connection = new NodeConnection();
        $paginator->paginate($qb, new PaginationRequest(null, 3, base64_encode('cursor:1')), $connection);

        self::assertEquals(2, $qb->getFirstResult());
        self::assertEquals(3, $qb->getMaxResults());
        self::assertEquals(5, $connection->getTotalCount());
        self::assertTrue($connection->getPageInfo()->isHasPreviousPage());
        self::assertFalse($connection->getPageInfo()->isHasNextPage());
        self::assertEquals($connection->getEdges()[0]->getCursor(), $connection->getPageInfo()->getStartCursor());
        self::assertEquals($connection->getEdges()[2]->getCursor(), $connection->getPageInfo()->getEndCursor());

        self::assertEquals(3, $connection->getEdges()[0]->getNode()->getId());
        self::assertEquals(4, $connection->getEdges()[1]->getNode()->getId());
        self::assertEquals(5, $connection->getEdges()[2]->getNode()->getId());

        self::assertEquals(base64_encode('cursor:2'), $connection->getEdges()[0]->getCursor());
        self::assertEquals(base64_encode('cursor:3'), $connection->getEdges()[1]->getCursor());
        self::assertEquals(base64_encode('cursor:4'), $connection->getEdges()[2]->getCursor());
    }

    public function testPaginateLastWithBefore()
    {
        $em = \Mockery::mock(EntityManagerInterface::class);

        /** @var QueryBuilder|Mock $qb */
        $qb = \Mockery::mock(QueryBuilder::class, [$em])->makePartial();

        $countQuery = \Mockery::mock(AbstractQuery::class);
        $countQuery->allows('getSingleScalarResult')->andReturn(5);

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->expects('getIdentifier')->andReturn(['id']);
        $em->expects('getClassMetadata')->andReturn($classMetadata);

        $countQuery->allows('execute')->andReturn(
            [
                new Post(1),
                new Post(2),
                new Post(3),
            ]
        );

        $qb->allows('getQuery')->andReturn($countQuery);

        $qb->select('p')
           ->from(Post::class, 'p')
           ->where('p.title = :title');

        $paginator = new DoctrineCursorPaginator($em);
        $connection = new NodeConnection();
        $paginator->paginate($qb, new PaginationRequest(null, 3, null, base64_encode('cursor:2')), $connection);

        self::assertEquals(0, $qb->getFirstResult());
        self::assertEquals(2, $qb->getMaxResults());
        self::assertEquals(5, $connection->getTotalCount());
        self::assertFalse($connection->getPageInfo()->isHasPreviousPage());
        self::assertTrue($connection->getPageInfo()->isHasNextPage());
        self::assertEquals($connection->getEdges()[0]->getCursor(), $connection->getPageInfo()->getStartCursor());
        self::assertEquals($connection->getEdges()[2]->getCursor(), $connection->getPageInfo()->getEndCursor());

        self::assertEquals(1, $connection->getEdges()[0]->getNode()->getId());
        self::assertEquals(2, $connection->getEdges()[1]->getNode()->getId());
        self::assertEquals(3, $connection->getEdges()[2]->getNode()->getId());

        self::assertEquals(base64_encode('cursor:0'), $connection->getEdges()[0]->getCursor());
        self::assertEquals(base64_encode('cursor:1'), $connection->getEdges()[1]->getCursor());
        self::assertEquals(base64_encode('cursor:2'), $connection->getEdges()[2]->getCursor());
    }
}
