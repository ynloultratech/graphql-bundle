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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Ynlo\GraphQLBundle\Annotation as API;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\TimestampableInterface;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\TimestampableTrait;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @UniqueEntity(fields={"username"}, message="The username <b>{{ value }}</b> is already taken")
 *
 * @API\ObjectType()
 *
 * @API\GetNode(fetchBy="login")
 * @API\ListNodes()
 * @API\AddNode()
 * @API\UpdateNode()
 * @API\DeleteNode()
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
     *
     * @API\Field(type="string!", name="login", description="")
     */
    protected $username;

    /**
     * @var Profile
     *
     * @ORM\OneToOne(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\Profile", inversedBy="user", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid()
     *
     * @API\Field("Profile!")
     * @API\InputInline()
     */
    protected $profile;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     *
     * @API\Field("boolean")
     */
    protected $enabled = true;

    /**
     * @var Post
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
}
