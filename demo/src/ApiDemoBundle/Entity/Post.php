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

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Ynlo\GraphQLBundle\Annotation as API;
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
 * @UniqueEntity(fields={"title"}, message="Already exist a post with this title")
 *
 * @ORM\HasLifecycleCallbacks()
 *
 * @API\ObjectType()
 *
 * @API\GetNode()
 * @API\ListNodes()
 * @API\AddNode()
 * @API\UpdateNode()
 * @API\DeleteNode()
 */
class Post implements NodeInterface, CommentableInterface, TimestampableInterface
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
     * @var string
     *
     * @ORM\Column(name="slug", type="string")
     *
     * @API\Field(type="string!", readOnly=true)
     */
    protected $slug;

    /**
     * @var User
     *
     * @Assert\NotNull()
     *
     * @ORM\ManyToOne(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @API\Field("User!")
     * @API\InputById()
     */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string")
     *
     * @API\Field("string!")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="string", nullable=true)
     *
     * @API\Field("string")
     */
    protected $body;

    /**
     * @var Collection|PostComment[]
     *
     * @ORM\OneToMany(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\PostComment", mappedBy="post", fetch="EXTRA_LAZY")
     *
     * @API\Field("[PostComment]")
     */
    protected $comments;

    /**
     * Post constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
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
    public function setAuthor(User $author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * @param string $title
     *
     * @return Post
     */
    public function setTitle(string $title): Post
    {
        $this->title = $title;
        $this->slug = Slugify::create()->slugify($title);

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return Post
     */
    public function setBody(?string $body): Post
    {
        $this->body = $body;

        return $this;
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
