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
class NodeConnectionEdge implements EdgeInterface
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
     * NodeConnectionEdge constructor.
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
     * {@inheritdoc}
     */
    public function getNode(): NodeInterface
    {
        return $this->node;
    }

    /**
     * {@inheritdoc}
     */
    public function setNode(NodeInterface $node)
    {
        $this->node = $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getCursor(): string
    {
        return $this->cursor;
    }

    /**
     * {@inheritdoc}
     */
    public function setCursor(string $cursor)
    {
        $this->cursor = $cursor;
    }
}
