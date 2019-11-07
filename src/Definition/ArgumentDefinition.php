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

use Ynlo\GraphQLBundle\Annotation\Argument;
use Ynlo\GraphQLBundle\Definition\Traits\DefinitionTrait;

/**
 * Class ArgumentDefinition
 */
class ArgumentDefinition implements DefinitionInterface
{
    use DefinitionTrait;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $nonNull = true;

    /**
     * @var bool
     */
    protected $list = false;

    /**
     * @var bool
     */
    protected $nonNullList = false;

    /**
     * @var mixed
     */
    protected $defaultValue = Argument::UNDEFINED_ARGUMENT;

    /**
     * Use when public argument name does not match with method name
     * e.g. userId (public) => $id (method), commonly used for reusable resolvers
     *
     * @var string
     */
    protected $internalName;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return ArgumentDefinition
     */
    public function setType(string $type): ArgumentDefinition
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNonNull(): bool
    {
        return $this->nonNull;
    }

    /**
     * @param bool $nonNull
     *
     * @return ArgumentDefinition
     */
    public function setNonNull(bool $nonNull): ArgumentDefinition
    {
        $this->nonNull = $nonNull;

        return $this;
    }

    /**
     * @return bool
     */
    public function isList(): bool
    {
        return $this->list;
    }

    /**
     * @param bool $list
     *
     * @return ArgumentDefinition
     */
    public function setList(bool $list): ArgumentDefinition
    {
        $this->list = $list;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNonNullList(): bool
    {
        return $this->nonNullList;
    }

    /**
     * @param bool $nonNullList
     */
    public function setNonNullList(bool $nonNullList)
    {
        $this->nonNullList = $nonNullList;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     *
     * @return ArgumentDefinition
     */
    public function setDefaultValue($defaultValue): ArgumentDefinition
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getInternalName(): ?string
    {
        return $this->internalName ?? $this->name;
    }

    /**
     * @param string $internalName
     *
     * @return ArgumentDefinition
     */
    public function setInternalName(?string $internalName): ArgumentDefinition
    {
        $this->internalName = $internalName;

        return $this;
    }
}
