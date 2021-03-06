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

use Ynlo\GraphQLBundle\Definition\Traits\ExecutableDefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\NodeAwareDefinitionTrait;

/**
 * Class QueryDefinition
 */
class QueryDefinition implements ExecutableDefinitionInterface
{
    use ExecutableDefinitionTrait;
}
