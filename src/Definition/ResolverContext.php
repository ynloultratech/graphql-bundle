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

use GraphQL\Type\Definition\ResolveInfo;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;

/**
 * Class ResolverContext
 */
class ResolverContext
{
    protected $root;

    protected $args = [];

    /**
     * @var QueryDefinition
     */
    protected $definition;

    /**
     * @var DefinitionManager
     */
    protected $definitionManager;

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
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args)
    {
        $this->args = $args;
    }

    /**
     * @return QueryDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param QueryDefinition $definition
     */
    public function setDefinition(QueryDefinition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return DefinitionManager
     */
    public function getDefinitionManager(): DefinitionManager
    {
        return $this->definitionManager;
    }

    /**
     * @param DefinitionManager $manager
     */
    public function setDefinitionManager(DefinitionManager $manager)
    {
        $this->definitionManager = $manager;
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
