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
use Ynlo\GraphQLBundle\Definition\Traits\DefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\ExtensionsAwareTrait;
use Ynlo\GraphQLBundle\Definition\Traits\FieldsAwareDefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\InterfacesAwareTrait;
use Ynlo\GraphQLBundle\Definition\Traits\NodeAwareDefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\ObjectDefinitionTrait;

/**
 * Class ObjectDefinition
 */
class ObjectDefinition implements
    ObjectDefinitionInterface,
    NodeAwareDefinitionInterface,
    HasExtensionsInterface,
    ImplementorInterface
{
    use DefinitionTrait;
    use FieldsAwareDefinitionTrait;
    use ClassAwareDefinitionTrait;
    use ObjectDefinitionTrait;
    use ExtensionsAwareTrait;
    use InterfacesAwareTrait;
    use NodeAwareDefinitionTrait;
}
