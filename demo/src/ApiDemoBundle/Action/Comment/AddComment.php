<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Action\Comment;

use Ynlo\GraphQLBundle\Action\AbstractNodeAction;
use Ynlo\GraphQLBundle\Annotation as API;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\User;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\CommentableInterface;
use Ynlo\GraphQLBundle\Model\AddNodePayload;

/**
 * @API\MutationAdd(node="Comment", args={
 *     @API\Arg(name="commentableId", type="ID!")
 * })
 */
class AddComment extends AbstractNodeAction
{
    /**
     * @param CommentableInterface $commentable
     * @param array                $input
     * @param bool                 $dryRun
     * @param null                 $clientMutationId
     *
     * @return AddNodePayload
     */
    public function __invoke(CommentableInterface $commentable, array $input, $dryRun = false, $clientMutationId = null): AddNodePayload
    {
        $comment = $commentable->createComment();
        $comment->setBody($input['body'] ?? null);

        //random
        $users = $this->getManager()->getRepository(User::class)->findAll();
        $comment->setAuthor($users[array_rand($users)]);

        $violations = $this->validate($comment);

        if ($violations) {
            $comment = null;
        } elseif (!$dryRun) {
            $this->getManager()->persist($comment);
            $this->getManager()->flush();
        }

        return new AddNodePayload($comment, $violations, $clientMutationId);
    }
}
