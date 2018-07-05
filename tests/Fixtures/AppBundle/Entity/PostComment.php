<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\CommentableInterface;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\CommentInterface;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\HasAuthorInterface;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\Message;

/**
 * @ORM\Entity()
 *
 * @GraphQL\ObjectType()
 */
class PostComment extends Message implements NodeInterface, CommentInterface
{
    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="comments")
     */
    protected $post;


    public function getId()
    {
        return 1;
    }

    public function setAuthor(User $author): HasAuthorInterface
    {
        // TODO: Implement setAuthor() method.
    }

    /**
     * @inheritDoc
     */
    public function getCommentable(): CommentableInterface
    {
        // TODO: Implement getCommentable() method.
    }

    public function setCommentable(CommentableInterface $commentable)
    {
        // TODO: Implement setCommentable() method.
    }

    /**
     * @inheritDoc
     */
    public function getBody(): string
    {
        // TODO: Implement getBody() method.
    }

    public function setBody(string $body)
    {
        // TODO: Implement setBody() method.
    }
}
