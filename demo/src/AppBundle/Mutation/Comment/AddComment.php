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

use Symfony\Component\Form\FormEvent;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\CommentableInterface;
use Ynlo\GraphQLBundle\Mutation\AddNode;

/**
 * @GraphQL\Mutation()
 */
class AddComment extends AddNode
{
    /**
     * {@inheritdoc}
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var CommentableInterface $commentable */
        $commentable = $event->getData()['commentable'];
        $comment = $commentable->createComment();
        $comment->setBody($event->getData()['body']);
        $event->setData($comment);
    }

    //    /**
    //     * {@inheritdoc}
    //     */
    //    protected function postValidation($data, ConstraintViolationList $violations)
    //    {
    //        $otherViolations = $this->getValidator()->validate($data);
    //        $violations->addViolationList($otherViolations);
    //    }
}
