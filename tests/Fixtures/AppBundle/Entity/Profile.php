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

/**
 * @ORM\Entity()
 *
 * @GraphQL\ObjectType()
 */
class Profile
{
    /**
     * @ORM\Column(type="string")
     *
     * @GraphQL\Expose()
     *
     * @var string
     */
    protected $nick;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $birthDate;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $single = true;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    protected $credits = 0.00;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $reputation = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $hobbies = [];

    /**
     * @var Collection
     *
     * @GraphQL\Expose()
     *
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="profile")
     */
    protected $photos;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User",  inversedBy="profile")
     */
    protected $user;

    /**
     * @ORM\ManyToMany(targetEntity="Topic")
     *
     * @var Collection
     */
    protected $favoriteTopics;

    /**
     * @return string
     */
    public function getNick(): string
    {
        return $this->nick;
    }

    /**
     * @param string $nick
     */
    public function setNick(string $nick): void
    {
        $this->nick = $nick;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return \DateTime
     */
    public function getBirthDate(): \DateTime
    {
        return $this->birthDate;
    }

    /**
     * @param \DateTime $birthDate
     */
    public function setBirthDate(\DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return bool
     */
    public function isSingle(): bool
    {
        return $this->single;
    }

    /**
     * @param bool $single
     */
    public function setSingle(bool $single): void
    {
        $this->single = $single;
    }

    /**
     * @return float
     */
    public function getCredits(): float
    {
        return $this->credits;
    }

    /**
     * @param float $credits
     */
    public function setCredits(float $credits): void
    {
        $this->credits = $credits;
    }

    /**
     * @return int
     */
    public function getReputation(): int
    {
        return $this->reputation;
    }

    /**
     * @param int $reputation
     */
    public function setReputation(int $reputation): void
    {
        $this->reputation = $reputation;
    }

    /**
     * @return string
     */
    public function getHobbies(): string
    {
        return $this->hobbies;
    }

    /**
     * @param string $hobbies
     */
    public function setHobbies(string $hobbies): void
    {
        $this->hobbies = $hobbies;
    }

    /**
     * @return Collection
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    /**
     * @param Collection $photos
     */
    public function setPhotos(Collection $photos): void
    {
        $this->photos = $photos;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Collection
     */
    public function getFavoriteTopics(): Collection
    {
        return $this->favoriteTopics;
    }

    /**
     * @param Collection $favoriteTopics
     */
    public function setFavoriteTopics(Collection $favoriteTopics): void
    {
        $this->favoriteTopics = $favoriteTopics;
    }
}