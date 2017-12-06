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

/**
 * Class DateTimeType
 */
class DateTimeType extends ScalarType
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $this->name = 'DateTime';
        $this->description = 'An ISO-8601 encoded UTC date string.';

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
        // Assuming internal representation of email is always correct:
        if ($value instanceof \DateTime) {
            return $value->format('c');
        }

        return $value;
    }

    /**
     * Parses an externally provided value (query variable) to use as an input
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseValue($value)
    {
        return \DateTime::createFromFormat('c', $value);
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
     *
     * E.g.
     * {
     *   user(email: "user@example.com")
     * }
     *
     * @param \GraphQL\Language\AST\Node $valueNode
     *
     * @return string
     */
    public function parseLiteral($valueNode)
    {
        return '';
    }
}
