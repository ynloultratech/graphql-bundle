<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type;

use GraphQL\Type\Definition\ScalarType;

class DynamicObjectType extends ScalarType
{
    public const DYNAMIC_OBJECT = 'DynamicObject';
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->name = self::DYNAMIC_OBJECT;
        $this->description = 'The `DynamicObject` represent a object with unknown and dynamic properties. 
For this type of objects you can\'t select the properties to get. 

A common usage of these type of objects is when the object represent a set of **key:value** pairs';

        parent::__construct([]);
    }

    /**
     * Serializes an internal value to include in a response.
     *
     * @param string $value
     *
     * @return string
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * Parses an externally provided value (query variable) to use as an input
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws \Error
     */
    public function parseValue($value)
    {
        return $value;
    }

    /**
     * @param \GraphQL\Language\AST\Node $valueNode
     *
     * @return string
     *
     * @throws \Error
     */
    public function parseLiteral($valueNode)
    {
        return $valueNode->value;
    }
}