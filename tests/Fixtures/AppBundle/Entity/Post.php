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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\CommentableInterface;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\Message;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\NodeNamedTrait;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Types\PostStatusType;

/**
 * @ORM\Entity()
 *
 * @GraphQL\ObjectType()
 * @GraphQL\QueryList(
 *     orderBy={
 *     "*",
 *     "rate",
 *     "category":"category.name",
 *     "user": "Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\OrderBy\Post\OrderByUser"
 * }
 * )
 * @GraphQL\MutationAdd()
 * @GraphQL\MutationUpdate()
 * @GraphQL\MutationDelete()
 * @GraphQL\MutationDeleteBatch()
 * @GraphQL\VirtualField(
 *     name="hasTags",
 *     type="bool",
 *     expression="!object.getTags()",
 *     description="Return true if the comment has tags"
 * )
 * @GraphQL\OverrideField(name="name", alias="title", description="Post title")
 */
class Post extends Message implements NodeInterface, CommentableInterface
{
    use NodeNamedTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(type="id")
     */
    protected $id;

    /**
     * @var Topic
     *
     * @ORM\ManyToOne(targetEntity="Topic", inversedBy="posts")
     */
    protected $topic;

    /**
     * @var string
     *
     * @GraphQL\Field(type="PostStatus")
     *
     * @ORM\Column(type="string")
     */
    protected $status = PostStatusType::DRAFT;

    /**
     * @var string
     *
     * @GraphQL\Field(type="PostType")
     *
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $private = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $views = 0;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array")
     */
    protected $tags = [];

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    protected $rate = 0.00;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Category")
     */
    protected $categories;

    /**
     * @var Collection|PostComment[]
     *
     * @ORM\OneToMany(targetEntity="PostComment", mappedBy="post", fetch="EXTRA_LAZY")
     */
    protected $comments;

    /**
     * @param int|null $id
     */
    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @GraphQL\Field(type="boolean")
     * @GraphQL\Argument(name="tag", type="string!", internalName="tagName")
     *
     * @param string $tagName
     *
     * @return bool
     */
    public function containsTag($tagName): bool
    {
        return \in_array($tagName, $this->tags);
    }

    /**
     * @inheritDoc
     */
    public function getComments(): Collection
    {
        // TODO: Implement getComments() method.
    }

    /**
     * @inheritDoc
     */
    public function setComments(Collection $comments)
    {
        // TODO: Implement setComments() method.
    }

    /**
     * @inheritDoc
     */
    public function createComment(): CommentableInterface
    {
        // TODO: Implement createComment() method.
    }
}
