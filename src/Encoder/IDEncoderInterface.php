<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Encoder;

use Ynlo\GraphQLBundle\Model\NodeInterface;

interface IDEncoderInterface
{
    /**
     * Encode given node into global ID string
     *
     * @param NodeInterface $node
     *
     * @return string|null
     */
    public function encode(NodeInterface $node): ?string;

    /**
     * Decode given global ID into real Node
     *
     * @param string $globalId
     *
     * @return NodeInterface|null
     */
    public function decode($globalId): ?NodeInterface;
}
