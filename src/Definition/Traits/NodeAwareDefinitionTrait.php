<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Traits;

use Ynlo\GraphQLBundle\Definition\NodeAwareDefinitionInterface;

/**
 * Trait TypeAwareDefinitionTrait
 */
trait NodeAwareDefinitionTrait
{
    /**
     * @var string
     */
    protected $node;

    /**
     * @return mixed
     */
    public function getNode():?string
    {
        return $this->node;
    }

    /**
     * @param string $node
     *
     * @return NodeAwareDefinitionInterface
     */
    public function setNode(?string $node): NodeAwareDefinitionInterface
    {
        $this->node = $node;

        return $this;
    }
}
