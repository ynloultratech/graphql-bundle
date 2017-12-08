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
 * Interface TypeAwareDefinitionInterface
 */
interface TypeAwareDefinitionInterface
{
    /**
     * @return mixed
     */
    public function getType();

    /**
     * @param mixed $type
     *
     * @return TypeAwareDefinitionInterface
     */
    public function setType($type): TypeAwareDefinitionInterface;

    /**
     * @return bool
     */
    public function isList(): bool;

    /**
     * @param bool $list
     *
     * @return TypeAwareDefinitionInterface
     */
    public function setList(bool $list): TypeAwareDefinitionInterface;

    /**
     * @return bool
     */
    public function isNonNull(): bool;

    /**
     * @param bool $nonNull
     *
     * @return TypeAwareDefinitionInterface
     */
    public function setNonNull(bool $nonNull): TypeAwareDefinitionInterface;

    /**
     * @return bool
     */
    public function isNonNullList(): bool;

    /**
     * @param bool $nonNullList
     *
     * @return TypeAwareDefinitionInterface
     */
    public function setNonNullList(bool $nonNullList): TypeAwareDefinitionInterface;
}
