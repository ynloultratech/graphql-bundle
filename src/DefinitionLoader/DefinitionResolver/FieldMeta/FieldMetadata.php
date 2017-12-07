<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver\FieldMeta;

/**
 * Class FieldMetadata
 */
class FieldMetadata
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $type;

    /**
     * e.i.: [String] -> list of elements or empty list
     *
     * @var bool|null
     */
    public $list;

    /**
     * e.i.: [String!] or [String!]! -> a non empty list of elements
     *
     * @var bool|null
     */
    public $nonNullList;

    /**
     * e.i.: String! or [String]! -> is a non empty value, but can be a empty list []
     *
     * @var bool|null
     */
    public $nonNull;

    /**
     * @var string
     */
    public $deprecationReason;

    /**
     * @param FieldMetadata $metadata
     */
    public function merge(FieldMetadata $metadata)
    {
        $refClass = new \ReflectionClass(__CLASS__);
        $props = $refClass->getProperties();
        foreach ($props as $prop) {
            $currentValue = $prop->getValue($this);
            $newValue = $prop->getValue($metadata);
            if (null === $currentValue) {
                $prop->setValue($this, $newValue);
            }
        }
    }
}
