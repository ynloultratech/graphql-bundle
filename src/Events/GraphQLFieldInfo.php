<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Events;

use GraphQL\Type\Definition\ResolveInfo;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;

class GraphQLFieldInfo
{
    /**
     * @var ResolveInfo
     */
    protected $info;

    /**
     * @var FieldsAwareDefinitionInterface
     */
    protected $object;

    /**
     * @var FieldDefinition
     */
    protected $field;

    /**
     * GraphQLFieldInfo constructor.
     *
     * @param FieldsAwareDefinitionInterface $object
     * @param FieldDefinition                $field
     * @param ResolveInfo                    $info
     */
    public function __construct(FieldsAwareDefinitionInterface $object, FieldDefinition $field, ResolveInfo $info)
    {
        $this->info = $info;
        $this->object = $object;
        $this->field = $field;
    }

    /**
     * @return ResolveInfo
     */
    public function getInfo(): ResolveInfo
    {
        return $this->info;
    }

    /**
     * @return FieldsAwareDefinitionInterface
     */
    public function getObject(): FieldsAwareDefinitionInterface
    {
        return $this->object;
    }

    /**
     * @return FieldDefinition
     */
    public function getField(): FieldDefinition
    {
        return $this->field;
    }
}
