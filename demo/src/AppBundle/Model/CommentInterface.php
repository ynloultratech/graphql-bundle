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
 * @GraphQL\MutationDelete(options={
 *  @GraphQL\Plugin\Endpoints("admin")
 * })
 */
interface CommentInterface extends NodeInterface, HasAuthorInterface, TimestampableInterface
{
    /**
     * @GraphQL\Field(type="Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User!")
     */
    public function getAuthor(): User;

    public function setAuthor(User $author): HasAuthorInterface;

    /**
     * @GraphQL\Field(type="Ynlo\GraphQLBundle\Demo\AppBundle\Model\CommentableInterface!")
     */
    public function getCommentable(): CommentableInterface;

    public function setCommentable(CommentableInterface $commentable);

    /**
     * @GraphQL\Field(type="string!")
     */
    public function getBody(): string;

    public function setBody(string $body);
}
