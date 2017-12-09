<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Traits;

use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;

/**
 * Trait FieldsAwareDefinitionTrait
 */
trait FieldsAwareDefinitionTrait
{
    /**
     * @var FieldDefinition[]
     */
    protected $fields = [];

    /**
     * @return array|FieldDefinition[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param string $name
     *
     * @return FieldDefinition
     */
    public function getField(string $name): FieldDefinition
    {
        return $this->fields[$name];
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasField(string $name): bool
    {
        return array_key_exists($name, $this->fields);
    }

    /**
     * @param FieldDefinition $field
     *
     * @return FieldsAwareDefinitionInterface
     */
    public function addField(FieldDefinition $field): FieldsAwareDefinitionInterface
    {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return FieldsAwareDefinitionInterface
     */
    public function removeField(string $name): FieldsAwareDefinitionInterface
    {
        unset($this->fields[$name]);

        return $this;
    }

    /**
     * @param FieldDefinition $field
     *
     * @return FieldsAwareDefinitionInterface
     */
    public function prependField(FieldDefinition $field): FieldsAwareDefinitionInterface
    {
        $this->fields = array_merge([$field->getName() => $field], $this->fields);

        return $this;
    }
}
