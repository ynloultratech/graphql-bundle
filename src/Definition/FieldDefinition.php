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
 * Class FieldDefinition
 */
class FieldDefinition implements DefinitionInterface, DeprecateInterface
{
    use DeprecateTrait;

    public const INPUT_BY_ID = 1;
    public const INPUT_BY_IDS = 2;
    public const INPUT_INLINE = 3;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $type;

    /**
     * e.i.: [String] -> list of elements or empty list
     *
     * @var bool
     */
    protected $list = false;

    /**
     * e.i.: [String!] or [String!]! -> a non empty list of elements
     *
     * @var bool
     */
    protected $nonNullList = false;

    /**
     * e.i.: String! or [String]! -> is a non empty value, but can be a empty list []
     *
     * @var bool
     */
    protected $nonNull = false;
    /**
     * Type of relation to use when this field is used for input operations
     *
     * @var int|null
     */
    protected $inputRelation;

    /**
     * The field is only fore reading purposes,
     * during the creation of any input based on object using this field
     * this field will be ignored.
     *
     * @var bool
     */
    protected $readOnly = false;

    /**
     * @var string
     */
    protected $originName;

    /**
     * @var string
     */
    protected $originType;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     */
    public function setList(bool $list)
    {
        $this->list = $list;
    }

    /**
     * @return bool
     */
    public function isNonNull(): bool
    {
        return $this->nonNull;
    }

    /**
     * @return null|int
     */
    public function getInputRelation():?int
    {
        return $this->inputRelation;
    }

    /**
     * @param null|int $inputRelation
     */
    public function setInputRelation(?int $inputRelation)
    {
        $this->inputRelation = $inputRelation;
    }

    /**
     * @param bool $nonNull
     */
    public function setNonNull(bool $nonNull)
    {
        $this->nonNull = $nonNull;
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
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * @param bool $readOnly
     */
    public function setReadOnly(bool $readOnly)
    {
        $this->readOnly = $readOnly;
    }

    /**
     * @return mixed
     */
    public function getOriginName()
    {
        return $this->originName;
    }

    /**
     * @param mixed $originName
     */
    public function setOriginName($originName)
    {
        $this->originName = $originName;
    }

    /**
     * @return mixed
     */
    public function getOriginType()
    {
        return $this->originType;
    }

    /**
     * @param mixed $originType
     */
    public function setOriginType($originType)
    {
        $this->originType = $originType;
    }
}
