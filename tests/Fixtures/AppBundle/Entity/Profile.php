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
}