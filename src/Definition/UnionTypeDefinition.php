<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition;

use Ynlo\GraphQLBundle\Definition\Traits\ClassAwareDefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\TypeAwareDefinitionTrait;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * UnionTypeDefinition
 */
class UnionTypeDefinition implements TypeAwareDefinitionInterface, ClassAwareDefinitionInterface
{
    use TypeAwareDefinitionTrait;
    use ClassAwareDefinitionTrait;

    /**
     * UnionTypeDefinition constructor.
     *
     * @param string|null $type
     */
    public function __construct(string $type = null)
    {
        if ($type) {
            $this->type = TypeUtil::normalize($type);
            $this->list = TypeUtil::isTypeList($type);
            $this->nonNull = TypeUtil::isTypeNonNull($type);
            $this->nonNullList = TypeUtil::isTypeNonNullList($type);
        }
    }
}
