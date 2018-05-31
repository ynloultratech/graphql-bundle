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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\TimestampableInterface;
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\TimestampableTrait;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @UniqueEntity(fields={"username"}, message="The username <b>{{ value }}</b> is already taken")
 *
 * @GraphQL\ObjectType(exclusionPolicy="ALL",
 *  options={
 *     @GraphQL\Plugin\Endpoints({"admin"})
 * })
 * @GraphQL\QueryList()
 * @GraphQL\MutationAdd()
 * @GraphQL\MutationUpdate()
 * @GraphQL\MutationDelete()
 */
class User extends BaseUser implements NodeInterface, TimestampableInterface
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
     * @GraphQL\Field(name="login", type="string")
     * @GraphQL\Expose()
     */
    protected $username;

    /**
     * @var string
     *
     * @GraphQL\Field(type="string")
     * @GraphQL\Expose()
     */
    protected $email;

    /**
     * @var bool
     *
     * @GraphQL\Field(type="bool")
     * @GraphQL\Expose()
     */
    protected $enabled;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string")
     *
     * @GraphQL\Expose()
     */
    protected $type = self::TYPE_USER;

    /**
     * @var Profile
     *
     * @ORM\OneToOne(targetEntity="Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Profile", inversedBy="user", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid()
     * @Assert\NotNull()
     *
     * @GraphQL\Expose()
     */
    protected $profile;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Post", mappedBy="author", fetch="EXTRA_LAZY")
     *
     * @GraphQL\Expose()
     */
    protected $posts;

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
     * @GraphQL\Argument(name="type", type="string!")
     */
    public function is($type): bool
    {
        return $this->getType() === $type;
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
        if (!$this->profile) {
            $this->profile = new Profile();
        }

        return $this->profile;
    }

    /**
     * @param Profile $profile
     */
    public function setProfile(?Profile $profile)
    {
        $this->profile = $profile;
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
