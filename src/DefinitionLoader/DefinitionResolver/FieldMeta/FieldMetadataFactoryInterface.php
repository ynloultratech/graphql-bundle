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
 * Interface FieldMetadataFactoryInterface
 */
interface FieldMetadataFactoryInterface
{
    /**
     * @param \ReflectionProperty|\ReflectionMethod $field
     *
     * @return FieldMetadata
     *
     * @throws \InvalidArgumentException if supplied argument is not a valid reflection property or method
     */
    public function getMetadataForField($field): FieldMetadata;
}
