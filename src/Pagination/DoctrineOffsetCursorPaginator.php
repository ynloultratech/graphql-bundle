<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Pagination;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Model\ConnectionInterface;
use Ynlo\GraphQLBundle\Model\NodeConnection;

/**
 * DoctrineOffsetCursorPaginator
 */
class DoctrineOffsetCursorPaginator implements DoctrineCursorPaginatorInterface
{
    /**
     * @var NodeConnection
     */
    protected $connection;

    /**
     * @var EntityManager|EntityManagerInterface
     */
    protected $entityManager;

    /**
     * DoctrineOffsetCursorPaginator constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(QueryBuilder $query, PaginationRequest $pagination, ConnectionInterface $connection)
    {
        $count = $this->getQueryTotal($query);
        $this->connection = $connection;
        $this->connection->setTotalCount($count);

        $this->applyCursor($query, $count, $pagination);

        $limit = $pagination->getFirst() ?? $pagination->getLast();
        $query->setMaxResults($limit);

        $results = $query->getQuery()->execute();

        $offset = $query->getFirstResult();

        $cursorOffset = $offset - 1;
        foreach ($results as $result) {
            $cursorOffset ++;

            if (!$this->connection->getPageInfo()->getStartCursor()) {
                $this->connection->getPageInfo()->setStartCursor($this->encodeCursor($offset));
            }

            $cursor = $this->encodeCursor($cursorOffset);
            $this->connection->addEdge($this->connection->createEdge($result, $cursor));
            if ($limit > 0) {
                $this->connection->setPages((int) ceil($count / $limit));
                if ($offset > 0) {
                    $this->connection->getPageInfo()->setPage((int) ceil($offset / $limit) + 1);
                } else {
                    $this->connection->getPageInfo()->setPage(1);
                }
            } else {
                $this->connection->setPages(0);
                $this->connection->getPageInfo()->setPage(0);
            }
            $this->connection->getPageInfo()->setEndCursor($cursor);
        }
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getQueryTotal(QueryBuilder $qb): int
    {
        $countQuery = clone $qb;

        if (\count($qb->getParameters()) > 0) {
            $countQuery->setParameters($qb->getParameters());
        }

        if ($countQuery->getDQLPart('orderBy')) {
            $countQuery->resetDQLPart('orderBy');
        }

        $countQuery->setMaxResults(null);
        $countQuery->setFirstResult(0);

        $rootEntity = $qb->getRootEntities()[0];
        $metadata = $this->entityManager->getClassMetadata($rootEntity);

        $queryAlias = $qb->getAllAliases()[0];
        $countQuery->select(sprintf('count(DISTINCT %s.%s) as total', $queryAlias, $metadata->getIdentifier()[0]));

        return $countQuery->getQuery()->getSingleScalarResult();
    }

    /**
     * @param QueryBuilder      $qb
     * @param int               $count
     * @param PaginationRequest $pagination
     */
    protected function applyCursor(QueryBuilder $qb, $count, PaginationRequest $pagination): void
    {
        $limit = $pagination->getFirst() ?? $pagination->getLast();
        $offset = 0;
        if (null !== $pagination->getBefore()) {
            $offset = $this->decodeCursor($pagination->getBefore()) - $limit;

            //when the offset is less than 0,
            //the limit of records will be modified to start in 0
            if ($offset < 0) {
                if ($pagination->getFirst()) {
                    $pagination->setFirst($pagination->getFirst() - abs($offset));
                } else {
                    $pagination->setLast($pagination->getLast() - abs($offset));
                }
                $offset = 0;
            }

            //first records before any cursor always start in 0
            if ($pagination->getFirst()) {
                $offset = 0;
            }
        } elseif (null !== $pagination->getAfter()) {
            //last records after any cursor always start in ($count - $limit)
            if ($pagination->getLast()) {
                $offset = $count - $pagination->getLast();
                if ($offset < $this->decodeCursor($pagination->getAfter())) {
                    $offset =  $this->decodeCursor($pagination->getAfter()) + 1;
                }
            } else {
                $offset = $this->decodeCursor($pagination->getAfter()) + 1;
            }
        }

        if ($pagination->getLast() && !$pagination->getBefore() && !$pagination->getAfter()) {
            $offset = $count - $pagination->getLast();
            if ($offset < 0) {
                $offset = 0;
            }
        }

        $page = $pagination->getPage();
        if ($page > 0) {
            $pages = (int) ceil($count / $limit);
            if ($page > $pages) {
                $page = $pages;
            }

            $offset = ($page - 1) * $limit;
            if ($offset < 0) {
                $offset = 0;
            }
        }

        if (0 === $offset) {
            $this->connection->getPageInfo()->setHasPreviousPage(false);
        } else {
            $this->connection->getPageInfo()->setHasPreviousPage(true);
        }

        if ($offset + $limit >= $count) {
            $this->connection->getPageInfo()->setHasNextPage(false);
        } else {
            $this->connection->getPageInfo()->setHasNextPage(true);
        }

        $qb->setFirstResult($offset);
    }

    /**
     * @param string $cursor
     *
     * @return int
     */
    protected function decodeCursor($cursor): int
    {
        [, $offset] = explode(':', base64_decode($cursor));

        return $offset;
    }

    /**
     * @param string $offset
     *
     * @return string
     */
    protected function encodeCursor($offset): string
    {
        return base64_encode(sprintf('cursor:%s', $offset));
    }
}
