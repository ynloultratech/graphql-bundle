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
 * Interface ArgumentAwareInterface
 */
interface ArgumentAwareInterface
{
    /**
     * @return ArgumentDefinition[]
     */
    public function getArguments(): array;

    /**
     * @param ArgumentDefinition[] $arguments
     *
     * @return ArgumentAwareInterface
     */
    public function setArguments(array $arguments): ArgumentAwareInterface;

    /**
     * @param string $name
     *
     * @return ArgumentDefinition
     */
    public function getArgument(string $name): ArgumentDefinition;

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasArgument(string $name): bool;

    /**
     * @param ArgumentDefinition $argument
     *
     * @return ArgumentAwareInterface
     */
    public function addArgument(ArgumentDefinition $argument): ArgumentAwareInterface;

    /**
     * @param string $name
     *
     * @return ArgumentAwareInterface
     */
    public function removeArgument(string $name): ArgumentAwareInterface;
}