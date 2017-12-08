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
 * @GraphQL\QueryGet(fetchBy="login")
 * @GraphQL\QueryGetAll()
 * @GraphQL\MutationAdd(form="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Form\CreateUserForm")
 * @GraphQL\MutationUpdate(form="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Form\UpdateUserForm")
 */
class User implements NodeInterface, TimestampableInterface
{
    use TimestampableTrait;

    public const TYPE_ADMIN = 'ADMIN';
    public const TYPE_USER = 'USER';

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
     * @Assert\Length(min="5")
     *
     * @GraphQL\Field(name="login")
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string")
     */
    protected $type = self::TYPE_USER;

    /**
     * @var Profile
     *
     * @ORM\OneToOne(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\Profile", inversedBy="user", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid()
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
     *
     * @GraphQL\Connection()
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
    public function setUsername(?string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return bool
     *
     * @GraphQL\Field(type="bool")
     */
    public function isAdmin(): bool
    {
        return $this->getType() === self::TYPE_ADMIN;
    }

    /**
     * @return bool
     *
     * @GraphQL\Field(type="bool")
     */
    public function isNormalUser(): bool
    {
        return $this->getType() === self::TYPE_USER;
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
