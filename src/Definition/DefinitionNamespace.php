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
 * DefinitionNamespace
 */
class DefinitionNamespace
{
    /**
     * @var string
     */
    protected $bundle;

    /**
     * @var string
     */
    protected $node;

    /**
     * DefinitionNamespace constructor.
     *
     * @param string $bundle
     * @param string $node
     */
    public function __construct(?string $bundle, ?string $node)
    {
        if (!$bundle && !$node) {
            throw new \InvalidArgumentException('Must define a bundle or node name to create a valid definition namespace');
        }
        $this->bundle = $bundle;
        $this->node = $node;
    }

    /**
     * @return string
     */
    public function getBundle(): ?string
    {
        return $this->bundle;
    }

    /**
     * @return string
     */
    public function getNode(): ?string
    {
        return $this->node;
    }
}
