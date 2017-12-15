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
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\CommentableInterface;
use Ynlo\GraphQLBundle\Mutation\AddNode;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * @GraphQL\Mutation()
 */
class AddComment extends AddNode
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
