<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model;

use Ynlo\GraphQLBundle\Annotation as API;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\User;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @API\InterfaceType()
 *
 * @API\DeleteNode(node="Comment")
 * @API\UpdateNode(node="Comment")
 */
interface CommentInterface extends NodeInterface
{
    /**
     * @return User
     *
     * @API\Field(type="User!")
     */
    public function getAuthor(): User;

    /**
     * @param User $author
     */
    public function setAuthor(User $author);

    /**
     * @return CommentableInterface
     *
     * @API\Field(type="Commentable!")
     */
    public function getCommentable(): CommentableInterface;

    /**
     * @param CommentableInterface $commentable
     */
    public function setCommentable(CommentableInterface $commentable);

    /**
     * @return string
     *
     * @API\Field(type="string!")
     */
    public function getBody(): string;

    /**
     * @param string $body
     */
    public function setBody(string $body);
}
