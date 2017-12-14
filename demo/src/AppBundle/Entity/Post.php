<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\CommentableInterface;
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\CommentableTrait;
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\CommentInterface;
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\TimestampableInterface;
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\TimestampableTrait;
use Ynlo\GraphQLBundle\Demo\AppBundle\Type\PostStatusType;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @UniqueEntity(fields={"title"}, message="Already exist a post with this title")
 *
 * @ORM\HasLifecycleCallbacks()
 *
 * @GraphQL\ObjectType()
 * @GraphQL\CRUDOperations()
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
     */
    protected $slug;

    /**
     * @var User
     *
     * @Assert\NotNull()
     *
     * @ORM\ManyToOne(targetEntity="Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $author;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Category", inversedBy="posts")
     *
     * @Assert\NotNull()
     * @Assert\Expression(expression="!this.getCategories().isEmpty()", message="Should have at least one category")
     */
    protected $categories;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     *
     * @GraphQL\Field(type="Ynlo\GraphQLBundle\Demo\AppBundle\Type\PostStatusType")
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="string", nullable=true)
     */
    protected $body;

    /**
     * @var Collection|PostComment[]
     *
     * @ORM\OneToMany(targetEntity="Ynlo\GraphQLBundle\Demo\AppBundle\Entity\PostComment", mappedBy="post", fetch="EXTRA_LAZY")
     */
    protected $comments;

    /**
     * Post constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->status = PostStatusType::DRAFT;
    }

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
    public function getAuthor(): ?User
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
    public function getTitle(): ?string
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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
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

    /**
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Collection $categories
     */
    public function setCategories($categories)
    {
        $this->categories = new ArrayCollection($categories);
    }
}
