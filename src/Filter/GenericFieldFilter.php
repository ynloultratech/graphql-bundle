<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter;

use Ynlo\GraphQLBundle\Definition\FieldDefinition;

abstract class GenericFieldFilter implements FieldAwareFilterInterface
{
    /**
     * @var FieldDefinition
     */
    private $field;

    /**
     * @param FieldDefinition $field
     */
    public function setField(FieldDefinition $field): void
    {
        $this->field = $field;
    }

    /**
     * @return FieldDefinition
     */
    public function getField(): FieldDefinition
    {
        return $this->field;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->field->getName();
    }

    /**
     * @return array
     */
    public function getEndpoints(): array
    {
        $endpointsConfig = $this->field->getMeta('endpoints');

        return $endpointsConfig['endpoints'] ?? [];
    }
}
