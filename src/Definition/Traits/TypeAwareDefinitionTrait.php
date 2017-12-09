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

use Ynlo\GraphQLBundle\Definition\TypeAwareDefinitionInterface;

/**
 * Trait TypeAwareDefinitionTrait
 */
trait TypeAwareDefinitionTrait
{
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
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return TypeAwareDefinitionInterface
     */
    public function setType($type): TypeAwareDefinitionInterface
    {
        $this->type = $type;

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
     * @return TypeAwareDefinitionInterface
     */
    public function setList(bool $list): TypeAwareDefinitionInterface
    {
        $this->list = $list;

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
     * @return TypeAwareDefinitionInterface
     */
    public function setNonNull(bool $nonNull): TypeAwareDefinitionInterface
    {
        $this->nonNull = $nonNull;

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
     *
     * @return TypeAwareDefinitionInterface
     */
    public function setNonNullList(bool $nonNullList): TypeAwareDefinitionInterface
    {
        $this->nonNullList = $nonNullList;

        return $this;
    }
}
