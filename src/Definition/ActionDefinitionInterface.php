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
 * Interface ActionDefinitionInterface
 */
interface ActionDefinitionInterface
{
    /**
     * @return string
     */
    public function getName(): ?string;

    /**
     * @param string $name
     *
     * @return ActionDefinitionInterface
     */
    public function setName(string $name): ActionDefinitionInterface;

    /**
     * @return string
     */
    public function getNodeType(): ?string;

    /**
     * @param string $type
     *
     * @return ActionDefinitionInterface
     */
    public function setNodeType($type): ActionDefinitionInterface;

    /**
     * @return string
     */
    public function getReturnType(): ?string;

    /**
     * @param string $type
     *
     * @return ActionDefinitionInterface
     */
    public function setReturnType($type): ActionDefinitionInterface;

    /**
     * @return bool
     */
    public function isReturnList(): bool;

    /**
     * @param bool $list
     *
     * @return ActionDefinitionInterface
     */
    public function setReturnList(bool $list): ActionDefinitionInterface;

    /**
     * @return string
     */
    public function getResolver():?string;

    /**
     * @param string $resolver
     *
     * @return ActionDefinitionInterface
     */
    public function setResolver(?string $resolver): ActionDefinitionInterface;

    /**
     * @return string
     */
    public function getDescription():?string;

    /**
     * @param string $description
     *
     * @return ActionDefinitionInterface
     */
    public function setDescription(?string $description): ActionDefinitionInterface;

    /**
     * @return array|ArgumentDefinition[]
     */
    public function getArgs(): array;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasArg(?string $name): bool;

    /**
     * @param string $name
     *
     * @return ArgumentDefinition
     */
    public function getArg(?string $name): ArgumentDefinition;

    /**
     * @param ArgumentDefinition $arg
     *
     * @return ActionDefinitionInterface
     */
    public function addArg(ArgumentDefinition $arg): ActionDefinitionInterface;
}
