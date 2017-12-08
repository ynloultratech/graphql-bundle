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
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\TimestampableInterface;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\TimestampableTrait;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @UniqueEntity(fields={"username"}, message="The username <b>{{ value }}</b> is already taken")
 *
 * @GraphQL\ObjectType()
 *
 * @GraphQL\QueryGet(fetchBy="username")
 * @GraphQL\QueryGetAll()
 * @GraphQL\MutationAdd()
 * @GraphQL\MutationUpdate()
 * @GraphQL\MutationDelete()
 */
class User implements NodeInterface, TimestampableInterface
{
    use TimestampableTrait;

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
     * @ORM\Column(name="username", type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="5", groups={"one"})
     */
    protected $username;

    /**
     * @var Profile
     *
     * @ORM\OneToOne(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\Profile", inversedBy="user", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid()
     *
     * @GraphQL\NonNull()
     */
    protected $profile;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled = true;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\Post", mappedBy="author", fetch="EXTRA_LAZY")
     */
    protected $posts;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->profile = new Profile();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return Profile
     */
    public function getProfile(): Profile
    {
        return $this->profile;
    }

    /**
     * @param Profile $profile
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @param Collection $posts
     */
    public function setPosts(Collection $posts)
    {
        $this->posts = $posts;
    }
}
