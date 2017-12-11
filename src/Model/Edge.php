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
class Edge
{
    /**
     * @var NodeInterface
     *
     * @GraphQL\Field(type="Ynlo\GraphQLBundle\Model\NodeInterface!")
     */
    protected $node;

    /**
     * @var string
     *
     * @GraphQL\Field(type="string!")
     */
    protected $cursor;

    /**
     * Edge constructor.
     *
     * @param NodeInterface $node
     * @param string        $cursor
     */
    public function __construct(NodeInterface $node, $cursor)
    {
        $this->node = $node;
        $this->cursor = $cursor;
    }

    /**
     * @return NodeInterface
     */
    public function getNode(): NodeInterface
    {
        return $this->node;
    }

    /**
     * @param NodeInterface $node
     */
    public function setNode(NodeInterface $node)
    {
        $this->node = $node;
    }

    /**
     * @return string
     */
    public function getCursor(): string
    {
        return $this->cursor;
    }

    /**
     * @param string $cursor
     */
    public function setCursor(string $cursor)
    {
        $this->cursor = $cursor;
    }
}
