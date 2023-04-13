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
 * Class DateType
 */
class DateType extends ScalarType
{
    public const DATE = 'Date';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $this->name = self::DATE;
        $this->description = 'An ISO-8601 encoded date string. Example: `1985-06-18`';

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
            return $value->format('Y-m-d');
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
        $date = \DateTime::createFromFormat('Y-m-d', $value);

        // add support to convert native javascript date object
        // Allow a client to use javascript Date object as input object of any date
        // the javascript date is represented as string like: 1985-06-18T06:20:00.000Z
        // NOTE: ony supported for input objects
        if (!$date && $value && preg_match('/Z$/', $value)) {
            $date = \DateTime::createFromFormat('U', strtotime($value));
        }

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
        if (!$date = \DateTime::createFromFormat('Y-m-d', $valueNode->value)) {
            throw new Error("Not a valid date", [$valueNode]);
        }

        return $valueNode->value;
    }
}
