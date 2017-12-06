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
 * Class QueryDefinition
 */
class QueryDefinition implements ActionDefinitionInterface, DeprecateInterface
{
    use DeprecateTrait;

    protected $name;

    protected $nodeType;

    protected $returnType;

    protected $returnList = false;

    protected $resolver;

    protected $description;

    protected $args = [];

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name): ActionDefinitionInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getNodeType(): ?string
    {
        return $this->nodeType;
    }

    /**
     * {@inheritDoc}
     */
    public function setNodeType($type): ActionDefinitionInterface
    {
        $this->nodeType = $type;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    /**
     * {@inheritDoc}
     */
    public function setReturnType($type): ActionDefinitionInterface
    {
        $this->returnType = $type;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isReturnList(): bool
    {
        return $this->returnList;
    }

    /**
     * {@inheritDoc}
     */
    public function setReturnList(bool $list): ActionDefinitionInterface
    {
        $this->returnList = $list;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getResolver():?string
    {
        return $this->resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function setResolver(?string $resolver): ActionDefinitionInterface
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription():?string
    {
        return $this->description;
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription(?string $description): ActionDefinitionInterface
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return array|ArgumentDefinition[]
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasArg(?string $name): bool
    {
        return (bool) ($this->args[$name] ?? false);
    }

    /**
     * @param string $name
     *
     * @return ArgumentDefinition
     */
    public function getArg(?string $name): ArgumentDefinition
    {
        return $this->args[$name];
    }

    /**
     * @param ArgumentDefinition $arg
     *
     * @return ActionDefinitionInterface
     */
    public function addArg(ArgumentDefinition $arg): ActionDefinitionInterface
    {
        $this->args[$arg->getName()] = $arg;

        return $this;
    }
}
