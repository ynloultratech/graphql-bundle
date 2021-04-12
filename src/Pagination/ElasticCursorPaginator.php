<?php
/*
 * ******************************************************************************
 * This file is part of the GraphQL Bundle package.
 *
 * (c) YnloUltratech <support@ynloultratech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  *****************************************************************************
 */

namespace Ynlo\GraphQLBundle\Pagination;

use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use FOS\ElasticaBundle\Repository;
use GraphQL\Error\UserError;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Ynlo\GraphQLBundle\Model\ConnectionInterface;

class ElasticCursorPaginator implements CursorPaginatorInterface
{
    protected Repository $finder;

    public function __construct(Repository $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @inheritDoc
     */
    public function paginate($query, PaginationRequest $pagination, ConnectionInterface $connection)
    {
        $limit = $pagination->getFirst() ?? $pagination->getLast();

        $pager = $this->finder->findPaginated($query)->setMaxPerPage($limit);
        if ($pagination->getPage()) {
            try {
                $pager->setCurrentPage($pagination->getPage());
            } catch (OutOfRangeCurrentPageException $exception) {
                $pager->setCurrentPage($pager->getNbPages());
            }
        } else {
            // TODO?
            throw new UserError('This list does not support cursor pagination.');
        }

        $connection->setPages($pager->getNbPages());
        $connection->setTotalCount($pager->getNbResults());
        $connection->getPageInfo()->setPage($pager->getCurrentPage());
        $connection->getPageInfo()->setHasPreviousPage($pager->hasPreviousPage());
        $connection->getPageInfo()->setHasNextPage($pager->hasNextPage());
        $connection->getPageInfo()->setStartCursor($this->encodeCursor($pager->getCurrentPageOffsetStart()));
        $connection->getPageInfo()->setEndCursor($this->encodeCursor($pager->getCurrentPageOffsetEnd()));

        $cursorOffset = $pager->getCurrentPageOffsetStart() - 1;
        foreach ($pager->getCurrentPageResults() as $result) {
            $cursorOffset++;
            $cursor = $this->encodeCursor($cursorOffset);
            $connection->addEdge($connection->createEdge($result, $cursor));
        }
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