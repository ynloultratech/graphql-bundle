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

/**
 * @ORM\Entity()
 *
 * @GraphQL\InterfaceType(
 *  exclusionPolicy="ALL",
 *  discriminatorProperty="type",
 *  discriminatorMap={"USER":"Customer", "ADMIN":"Administrator"},
 *  options={
 *     @GraphQL\Plugin\Endpoints({"admin"})
 * })
 * @GraphQL\ObjectType(name="Customer", exclusionPolicy="ALL")
 * @GraphQL\ObjectType(name="Administrator", exclusionPolicy="ALL")
 * @GraphQL\QueryList()
 * @GraphQL\MutationAdd()
 * @GraphQL\MutationUpdate()
 * @GraphQL\MutationDelete()
 * @GraphQL\MutationDeleteBatch()
 */
class User implements NodeInterface
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @GraphQL\Field(name="login")
     * @GraphQL\Expose()
     *
     * @var string|null
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     *
     * @GraphQL\Expose()
     *
     * @var string|null
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     *
     * @var string|null
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var Profile|null
     *
     * @GraphQL\Expose()
     *
     * @ORM\OneToOne(targetEntity="Profile", mappedBy="user")
     */
    protected $profile;

    /**
     * @ORM\Column(type="datetime")
     *
     * @GraphQL\Expose()
     *
     * @var \DateTime|null
     */
    protected $lastLogin;

    /**
     * User constructor.
     *
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
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param null|string $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param null|string $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
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
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return null|Profile
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    /**
     * @param null|Profile $profile
     */
    public function setProfile(?Profile $profile): void
    {
        $this->profile = $profile;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime|null $lastLogin
     */
    public function setLastLogin(?\DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }
}
