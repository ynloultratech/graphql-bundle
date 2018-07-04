<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\NodeAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

final class ContextBuilder
{
    /**
     * @var Endpoint
     */
    private $endpoint;

    /**
     * @var ExecutableDefinitionInterface
     */
    private $definition;

    /**
     * @var mixed
     */
    private $root;

    /**
     * @var ObjectDefinitionInterface
     */
    private $node;

    /**
     * Array of arguments given
     *
     * @var array
     */
    private $args = [];

    /**
     * @var ResolveInfo
     */
    private $resolveInfo;

    /**
     * ContextBuilder constructor.
     *
     * @param Endpoint $endpoint
     */
    private function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param Endpoint $endpoint
     *
     * @return ContextBuilder
     */
    public static function create(Endpoint $endpoint)
    {
        return new self($endpoint);
    }

    /**
     * @return ResolverContext
     */
    public function build(): ResolverContext
    {
        return new ResolverContext(
            $this->endpoint,
            $this->definition,
            $this->root,
            $this->node,
            $this->args,
            $this->resolveInfo
        );
    }

    /**
     * @param ExecutableDefinitionInterface $definition
     *
     * @return ContextBuilder
     */
    public function setDefinition(ExecutableDefinitionInterface $definition): ContextBuilder
    {
        $this->definition = $definition;

        $type = null;
        if ($definition instanceof NodeAwareDefinitionInterface && $definition->getNode()) {
            $type = $definition->getNode();
        }

        if (!$type) {
            $type = $definition->getType();
        }

        if ($this->endpoint->hasType($type)
            && ($nodeDefinition = $this->endpoint->getType($type))
            && ($nodeDefinition instanceof ObjectDefinition || $nodeDefinition instanceof InterfaceDefinition)) {
            $this->node = $nodeDefinition;
        }

        return $this;
    }

    /**
     * @param mixed $root
     *
     * @return ContextBuilder
     */
    public function setRoot($root): ContextBuilder
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @param array $args
     *
     * @return ContextBuilder
     */
    public function setArgs(array $args): ContextBuilder
    {
        $this->args = $args;

        return $this;
    }

    /**
     * @param ResolveInfo $resolveInfo
     *
     * @return ContextBuilder
     */
    public function setResolveInfo(ResolveInfo $resolveInfo): ContextBuilder
    {
        $this->resolveInfo = $resolveInfo;

        return $this;
    }
}
