<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Mutation\Comment;

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\CommentableInterface;
use Ynlo\GraphQLBundle\Mutation\AddNodeMutation;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * @GraphQL\Mutation(payload="Ynlo\GraphQLBundle\Model\AddNodePayload")
 */
class AddComment extends AddNodeMutation
{
    /**
     * {@inheritdoc}
     */
    protected function onSubmit($inputSource, &$normData)
    {
        /** @var CommentableInterface $commentable */
        $commentable = $normData['commentable'];
        $comment = $commentable->createComment();
        $comment->setAuthor($normData['author']);
        $comment->setBody($normData['body']);

        $normData = $comment;
    }

    /**
     * {@inheritdoc}
     */
    protected function postValidation($data, ConstraintViolationList $violations)
    {
        $otherViolations = $this->getValidator()->validate($data);
        $violations->addViolationList($otherViolations);
    }
}
