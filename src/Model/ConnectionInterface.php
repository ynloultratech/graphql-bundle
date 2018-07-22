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
 * @GraphQL\InterfaceType()
 */
interface ConnectionInterface
{
    /**
     * @GraphQL\Field(type="int!")
     *
     * @return int
     */
    public function getTotalCount();

    /**
     * @param int $totalCount
     */
    public function setTotalCount(int $totalCount);

    /**
     * @GraphQL\Field(type="int!")
     *
     * @return int
     */
    public function getPages(): int;

    /**
     * @param int $pages
     *
     * @return ConnectionInterface
     */
    public function setPages(int $pages): ConnectionInterface;

    /**
     * @param EdgeInterface $edge
     */
    public function addEdge(EdgeInterface $edge);

    /**
     * @param NodeInterface $node
     * @param string        $cursor
     */
    public function createEdge(NodeInterface $node, string $cursor);

    /**
     * @GraphQL\Field(type="[Ynlo\GraphQLBundle\Model\EdgeInterface]")
     *
     * @return array
     */
    public function getEdges(): array;

    /**
     * @param array $edges
     */
    public function setEdges(array $edges);

    /**
     * @GraphQL\Field(type="Ynlo\GraphQLBundle\Model\PageInfo")
     *
     * @return PageInfo
     */
    public function getPageInfo(): PageInfo;

    /**
     * @param PageInfo $pageInfo
     */
    public function setPageInfo(PageInfo $pageInfo);
}
