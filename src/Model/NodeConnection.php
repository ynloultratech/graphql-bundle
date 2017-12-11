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
class NodeConnection
{
    /**
     * @var int
     *
     * @GraphQL\Field(type="Int!")
     */
    protected $totalCount = 0;

    /**
     * @var array
     *
     * @GraphQL\Field(type="[Ynlo\GraphQLBundle\Model\Edge]")
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
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     */
    public function setTotalCount(int $totalCount)
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @param NodeInterface $node
     * @param string        $cursor
     */
    public function addEdge(NodeInterface $node, string $cursor)
    {
        $this->edges[] = new Edge($node, $cursor);
    }

    /**
     * @return array
     */
    public function getEdges(): array
    {
        return $this->edges;
    }

    /**
     * @param array $edges
     */
    public function setEdges(array $edges)
    {
        $this->edges = $edges;
    }

    /**
     * @return PageInfo
     */
    public function getPageInfo(): PageInfo
    {
        return $this->pageInfo;
    }

    /**
     * @param PageInfo $pageInfo
     */
    public function setPageInfo(PageInfo $pageInfo)
    {
        $this->pageInfo = $pageInfo;
    }
}
