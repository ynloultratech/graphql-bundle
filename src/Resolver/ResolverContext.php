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
use Ynlo\GraphQLBundle\Definition\MetaAwareInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Definition\Traits\MetaAwareTrait;

/**
 * Context used for resolvers
 */
class ResolverContext implements MetaAwareInterface
{
    use MetaAwareTrait;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @var ExecutableDefinitionInterface
     */
    protected $definition;

    /**
     * @var mixed
     */
    protected $root;

    /**
     * @var ObjectDefinitionInterface
     */
    protected $node;

    /**
     * Array of arguments given
     *
     * @var array
     */
    protected $args = [];

    /**
     * @var ResolveInfo
     */
    protected $resolveInfo;

    /**
     * ResolverContext constructor.
     *
     * @param Endpoint                      $endpoint
     * @param ExecutableDefinitionInterface $definition
     * @param mixed                         $root
     * @param ObjectDefinitionInterface     $node
     * @param array                         $args
     * @param ResolveInfo                   $resolveInfo
     */
    public function __construct(
        Endpoint $endpoint,
        ?ExecutableDefinitionInterface $definition = null,
        $root = null,
        ?ObjectDefinitionInterface $node = null,
        array $args = [],
        ?ResolveInfo $resolveInfo = null,
        ?array $metas = []
    ) {
        $this->endpoint = $endpoint;
        $this->definition = $definition;
        $this->root = $root;
        $this->node = $node;
        $this->args = $args;
        $this->resolveInfo = $resolveInfo;
        $this->metas = $metas;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        return $this->endpoint;
    }

    /**
     * @return ExecutableDefinitionInterface
     */
    public function getDefinition(): ExecutableDefinitionInterface
    {
        return $this->definition;
    }

    /**
     * @return mixed|null
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @deprecated use getNode() instead
     *
     * @return ObjectDefinitionInterface|null
     */
    public function getNodeDefinition(): ?ObjectDefinitionInterface
    {
        return $this->getNode();
    }

    /**
     * @return ObjectDefinitionInterface|null
     */
    public function getNode(): ?ObjectDefinitionInterface
    {
        return $this->node;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @return ResolveInfo
     */
    public function getResolveInfo(): ResolveInfo
    {
        return $this->resolveInfo;
    }
}
