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
use Symfony\Component\Validator\Constraints as Assert;
use Ynlo\GraphQLBundle\Annotation as API;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @API\ObjectType()
 */
class Profile
{
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
     * @ORM\OneToOne(targetEntity="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\User", mappedBy="profile")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", nullable=true)
     *
     * @API\Field("string")
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", nullable=true)
     *
     * @API\Field("string")
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", nullable=true)
     *
     * @API\Field("string")
     */
    protected $phone;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     *
     * @ORM\Column(name="email", type="string")
     *
     * @API\Field("string")
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="twitter", type="string", nullable=true)
     *
     * @Assert\Regex(pattern="/^#/", message="Its not a valid twitter user")
     *
     * @API\Field("string")
     */
    protected $twitter;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook", type="string", nullable=true)
     *
     * @API\Field("string")
     */
    protected $facebook;

    /**
     * @var Address
     *
     * @ORM\Embedded(class="Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\Address")
     *
     * @Assert\Valid()
     *
     * @API\Field("Address")
     * @API\InputInline()
     */
    protected $address;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->address = new Address();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return Profile
     */
    public function setPhone(?string $phone): Profile
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    /**
     * @param string $twitter
     *
     * @return Profile
     */
    public function setTwitter(?string $twitter): Profile
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    /**
     * @param string $facebook
     *
     * @return Profile
     */
    public function setFacebook(?string $facebook): Profile
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
    }
}
