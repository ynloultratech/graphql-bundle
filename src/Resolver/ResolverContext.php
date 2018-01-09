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
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Context used for resolvers
 */
class ResolverContext
{
    /**
     * @var mixed
     */
    protected $root;

    /**
     * @var ObjectDefinitionInterface
     */
    protected $nodeDefinition;

    /**
     * Array of arguments given
     *
     * @var array
     */
    protected $args = [];

    /**
     * @var ExecutableDefinitionInterface
     */
    protected $definition;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @var ResolveInfo
     */
    protected $resolveInfo;

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return ObjectDefinitionInterface
     */
    public function getNodeDefinition(): ObjectDefinitionInterface
    {
        return $this->nodeDefinition;
    }

    /**
     * @param ObjectDefinitionInterface $nodeDefinition
     */
    public function setNodeDefinition(ObjectDefinitionInterface $nodeDefinition)
    {
        $this->nodeDefinition = $nodeDefinition;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @return mixed
     */
    public function getArg(string $name)
    {
        return $this->args[$name];
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args)
    {
        $this->args = $args;
    }

    /**
     * @return ExecutableDefinitionInterface
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param ExecutableDefinitionInterface $definition
     */
    public function setDefinition(ExecutableDefinitionInterface $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        return $this->endpoint;
    }

    /**
     * @param Endpoint $endpoint
     */
    public function setEndpoint(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return ResolveInfo
     */
    public function getResolveInfo(): ResolveInfo
    {
        return $this->resolveInfo;
    }

    /**
     * @param ResolveInfo $resolveInfo
     */
    public function setResolveInfo(ResolveInfo $resolveInfo)
    {
        $this->resolveInfo = $resolveInfo;
    }
}
