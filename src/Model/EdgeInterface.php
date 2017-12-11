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
interface EdgeInterface
{
    /**
     * @GraphQL\Field(type="Ynlo\GraphQLBundle\Model\NodeInterface!")
     *
     * @return NodeInterface
     */
    public function getNode(): NodeInterface;

    /**
     * @param NodeInterface $node
     */
    public function setNode(NodeInterface $node);

    /**
     * @GraphQL\Field(type="string!")
     *
     * @return string
     */
    public function getCursor(): string;

    /**
     * @param string $cursor
     */
    public function setCursor(string $cursor);
}
