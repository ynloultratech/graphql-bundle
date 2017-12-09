<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\CommentableInterface;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\CommentableTrait;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\CommentInterface;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\TimestampableInterface;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\TimestampableTrait;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 *
 * @UniqueEntity(fields={"post", "author", "body"}, message="Duplicate comment", errorPath="body")
 * @UniqueEntity(fields={"parentComment", "author", "body"}, message="Duplicate comment")
 */
class PostComment implements NodeInterface, CommentInterface, CommentableInterface, TimestampableInterface
{
    use TimestampableTrait;
    use CommentableTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var User
     *
     * @Assert\NotNull()
     *
     * @ORM\ManyToOne(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $author;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\Post", inversedBy="comments")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @GraphQL\Exclude()
     */
    protected $post;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="body", type="string")
     */
    protected $body;

    /**
     * @var Collection|PostComment
     *
     * @ORM\ManyToOne(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\PostComment", inversedBy="comments")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @GraphQL\Exclude()
     */
    protected $parentComment;

    /**
     * @var Collection|PostComment[]
     *
     * @ORM\OneToMany(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\PostComment", mappedBy="parentComment", fetch="EXTRA_LAZY")
     */
    protected $comments;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author
     */
    public function setAuthor(?User $author)
    {
        $this->author = $author;
    }

    /**
     * {@inheritDoc}
     */
    public function getCommentable(): CommentableInterface
    {
        if ($this->parentComment) {
            return $this->parentComment;
        }

        return $this->post;
    }

    /**
     * {@inheritDoc}
     */
    public function setCommentable(CommentableInterface $commentable)
    {
        if ($commentable instanceof Post) {
            $this->post = $commentable;
        } else {
            $this->parentComment = $commentable;
        }
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(?string $body)
    {
        $this->body = $body;
    }

    /**
     * {@inheritDoc}
     */
    public function createComment(): CommentInterface
    {
        $comment = new PostComment();
        $comment->setCommentable($this);

        return $comment;
    }
}
