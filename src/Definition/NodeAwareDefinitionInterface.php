<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition;

/**
 * NodeAwareDefinitionInterface
 */
interface NodeAwareDefinitionInterface
{
    /**
     * @param string $node
     *
     * @return NodeAwareDefinitionInterface
     */
    public function setNode(?string $node): NodeAwareDefinitionInterface;

    /**
     * @return null|string
     */
    public function getNode(): ?string;
}
