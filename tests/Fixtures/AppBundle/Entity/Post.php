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
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\Message;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\NodeNamedTrait;

/**
 * @ORM\Entity()
 *
 * @GraphQL\ObjectType()
 * @GraphQL\QueryList()
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
class Post extends Message implements NodeInterface
{
    use NodeNamedTrait;

    /**
     * @ORM\Column(type="simple_array")
     *
     * @var string[]
     */
    protected $tags = [];

    /**
     * @var Collection|Comment[]
     *
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post", fetch="EXTRA_LAZY")
     */
    protected $comments;

    public function getId()
    {
        return 1;
    }

    /**
     * @GraphQL\Field(type="boolean")
     * @GraphQL\Argument(name="tag", type="string!", internalName="tagName")
     *
     * @param string $tag
     *
     * @return bool
     */
    public function containsTag($tagName)
    {
        return \in_array($tagName, $this->tags);
    }
}
