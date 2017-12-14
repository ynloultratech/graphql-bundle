<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Mutation\Comment;

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\AddNodePayload;

/**
 * @GraphQL\ObjectType()
 * @GraphQL\OverrideField(name="node", type="Ynlo\GraphQLBundle\Demo\AppBundle\Model\CommentInterface")
 */
class AddCommentPayload extends AddNodePayload
{

}
