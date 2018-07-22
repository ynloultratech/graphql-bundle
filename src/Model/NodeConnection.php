<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @GraphQL\ObjectType()
 */
class NodeConnection implements ConnectionInterface
{
    /**
     * @var int
     *
     * @GraphQL\Field(type="Int!")
     */
    protected $totalCount = 0;

    /**
     * @var int
     *
     * @GraphQL\Field(type="Int!")
     */
    protected $pages = 0;

    /**
     * @var array
     *
     * @GraphQL\Field(type="[Ynlo\GraphQLBundle\Model\EdgeInterface]")
     */
    protected $edges = [];

    /**
     * @var PageInfo
     *
     * @GraphQL\Field(type="Ynlo\GraphQLBundle\Model\PageInfo!")
     */
    protected $pageInfo;

    /**
     * NodeConnection constructor.
     */
    public function __construct()
    {
        $this->pageInfo = new PageInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount(int $totalCount)
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @return int
     */
    public function getPages(): int
    {
        return $this->pages;
    }

    /**
     * @param int $pages
     *
     * @return NodeConnection
     */
    public function setPages(int $pages): ConnectionInterface
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addEdge(EdgeInterface $edge)
    {
        $this->edges[] = $edge;
    }

    /**
     * {@inheritdoc}
     */
    public function createEdge(NodeInterface $node, string $cursor)
    {
        return new NodeConnectionEdge($node, $cursor);
    }

    /**
     * {@inheritdoc}
     */
    public function getEdges(): array
    {
        return $this->edges;
    }

    /**
     * {@inheritdoc}
     */
    public function setEdges(array $edges)
    {
        $this->edges = $edges;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageInfo(): PageInfo
    {
        return $this->pageInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageInfo(PageInfo $pageInfo)
    {
        $this->pageInfo = $pageInfo;
    }
}
