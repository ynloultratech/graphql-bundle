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
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * The `Upload` special type represents a file to be uploaded in the same HTTP request as specified by
 * @see https://github.com/jaydenseric/graphql-multipart-request-spec
 */
class UploadType extends ScalarType
{
    /**
     * @var string
     */
    public $name = 'Upload';

    /**
     * @var string
     */
    public $description =
        'The `Upload` special type represents a file to be uploaded in the same HTTP request as specified by
 [graphql-multipart-request-spec](https://github.com/jaydenseric/graphql-multipart-request-spec).';

    /**
     * {@inheritdoc}
     */
    public function serialize($value)
    {
        throw new InvariantViolation('`Upload` cannot be serialized');
    }

    /**
     * {@inheritdoc}
     */
    public function parseValue($value)
    {
        if (!$value instanceof UploadedFile) {
            throw new \UnexpectedValueException(
                'Could not get uploaded file, be sure to conform to GraphQL multipart request specification. 
Instead got: '.Utils::printSafe($value)
            );
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function parseLiteral($valueNode)
    {
        throw new Error(
            '`Upload` cannot be hardcoded in query, be sure to conform to GraphQL multipart request specification.
Instead got: '.$valueNode->kind, [$valueNode]
        );
    }
}
