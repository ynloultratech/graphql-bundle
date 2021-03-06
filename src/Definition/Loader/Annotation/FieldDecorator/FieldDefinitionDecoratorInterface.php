<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation\FieldDecorator;

use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;

/**
 * This interface is used by ObjectTypeAnnotationParser to decorate definition of fields
 */
interface FieldDefinitionDecoratorInterface
{
    /**
     * @param \ReflectionProperty|\ReflectionMethod $field
     * @param FieldDefinition                       $definition
     * @param ObjectDefinitionInterface             $objectDefinition
     */
    public function decorateFieldDefinition($field, FieldDefinition $definition, ObjectDefinitionInterface $objectDefinition);
}
