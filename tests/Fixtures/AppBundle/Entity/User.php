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
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var Profile
     *
     * @ORM\OneToOne(targetEntity="Profile", mappedBy="user")
     */
    protected $profile;

    /**
     * @ORM\Column(type="datetime")
     *
     * @GraphQL\Expose()
     *
     * @var \DateTime
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
}
