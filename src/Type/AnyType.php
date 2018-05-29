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

use GraphQL\Error\Error;
use GraphQL\Type\Definition\ScalarType;

class AnyType extends ScalarType
{
    public const ANY = 'Any';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $this->name = self::ANY;
        $this->description = 'The `Any` type can represent different type of scalars like string, integer, boolean etc.';

        parent::__construct($config);
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
     * @throws Error
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
     * @throws Error
     */
    public function parseLiteral($valueNode)
    {
        return $valueNode->value;
    }
}
