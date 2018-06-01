<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Exception\GraphQL;

use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;

class ForbiddenFieldException extends SecurityException
{
    public function __construct(FieldsAwareDefinitionInterface $object, FieldDefinition $field, $message = null)
    {
        if (!$message) {
            $message = sprintf(
                'Does not have enough permissions to get %s.%s.',
                $object->getName(),
                $field->getName()
            );
        }
        parent::__construct($message);
    }
}
