<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @GraphQL\InterfaceType()
 * @GraphQL\CRUDOperations(include={"delete"})
 */
interface CommentInterface extends NodeInterface
{
    /**
     * @return User
     *
     * @GraphQL\Field(type="Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User!")
     */
    public function getAuthor(): User;

    /**
     * @param User $author
     */
    public function setAuthor(User $author);

    /**
     * @return CommentableInterface
     *
     * @GraphQL\Field(type="Ynlo\GraphQLBundle\Demo\AppBundle\Model\CommentableInterface!")
     */
    public function getCommentable(): CommentableInterface;

    /**
     * @param CommentableInterface $commentable
     */
    public function setCommentable(CommentableInterface $commentable);

    /**
     * @return string
     *
     * @GraphQL\Field(type="string!")
     */
    public function getBody(): string;

    /**
     * @param string $body
     */
    public function setBody(string $body);
}
