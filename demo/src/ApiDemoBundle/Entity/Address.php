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
 * @ORM\Embeddable()
 *
 * @API\ObjectType()
 */
class Address
{
    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", nullable=true)
     *
     * @API\Field("string")
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", nullable=true)
     *
     * @API\Field("string")
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", nullable=true)
     *
     * @API\Field("string")
     */
    protected $state;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", nullable=true)
     *
     * @Assert\Length(min="5", max="5")
     *
     * @API\Field("string")
     */
    protected $zipCode;

    /**
     * @return string
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return Address
     */
    public function setStreet(?string $street): Address
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Address
     */
    public function setCity(?string $city): Address
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return Address
     */
    public function setState(?string $state): Address
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     *
     * @return Address
     */
    public function setZipCode(?string $zipCode): Address
    {
        $this->zipCode = $zipCode;

        return $this;
    }
}
