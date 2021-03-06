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
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;

/**
 * Class TimeType
 */
class TimeType extends ScalarType
{
    public const TIME = 'Time';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $this->name = self::TIME;
        $this->description = 'An ISO-8601 encoded time string. Example: `13:18:05`';

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
            return $value->format('H:i:s');
        }

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
        $date = \DateTime::createFromFormat('H:i:s', $value);
        if (!$date) {
            throw new Error(sprintf("Cannot represent following value as date: %s", Utils::printSafeJson($value)));
        }

        return $date;
    }

    /**
     * @inheritDoc
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if (!$valueNode instanceof StringValueNode) {
            throw new Error(sprintf('Query error: Can only parse strings got: %s', $valueNode->kind), [$valueNode]);
        }
        if (!$date = \DateTime::createFromFormat('H:i:s', $valueNode->value)) {
            throw new Error("Not a valid date", [$valueNode]);
        }

        return $valueNode->value;
    }
}
