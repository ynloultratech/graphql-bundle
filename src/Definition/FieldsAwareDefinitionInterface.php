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
 * Interface FieldsAwareDefinitionInterface
 */
interface FieldsAwareDefinitionInterface extends DefinitionInterface
{
    /**
     * @return array|FieldDefinition[]
     */
    public function getFields(): array;

    /**
     * @param string $name
     *
     * @return FieldDefinition
     */
    public function getField(string $name): FieldDefinition;

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasField(string $name): bool;


    /**
     * @param FieldDefinition $field
     *
     * @return FieldsAwareDefinitionInterface
     */
    public function addField(FieldDefinition $field): FieldsAwareDefinitionInterface;

    /**
     * @param string $name
     *
     * @return FieldsAwareDefinitionInterface
     */
    public function removeField(string $name): FieldsAwareDefinitionInterface;

    /**
     * @param FieldDefinition $field
     *
     * @return FieldsAwareDefinitionInterface
     */
    public function prependField(FieldDefinition $field): FieldsAwareDefinitionInterface;
}
