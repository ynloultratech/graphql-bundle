<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;

/**
 * @GraphQL\InterfaceType()
 */
abstract class Message implements HasAuthorInterface
{
    /**
     * @GraphQL\Field(type="string")
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $body;

    /**
     * @GraphQL\Field(type="datetime")
     *
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @GraphQL\Exclude()
     *
     * @var User
     *
     * @ORM\ManyToMany(targetEntity="\Ynlo\GraphQLBundle\Tests\Fixtures\App\Entity\User")
     */
    protected $user;

    /**
     * @inheritDoc
     */
    public function getAuthor(): User
    {
        return $this->user;
    }
}
