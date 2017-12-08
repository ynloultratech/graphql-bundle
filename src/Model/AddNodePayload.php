<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Model;

use Ynlo\GraphQLBundle\Annotation as API;

/**
 * @API\ObjectType()
 * @API\OverrideField(name="node", description="Added node instance")
 */
class AddNodePayload extends UpdateNodePayload
{
}
