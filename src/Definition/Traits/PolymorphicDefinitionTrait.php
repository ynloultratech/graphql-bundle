<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Traits;

use Ynlo\GraphQLBundle\Definition\PolymorphicDefinitionInterface;

trait PolymorphicDefinitionTrait
{
    /**
     * Key:Value pair array containing the source and expected type
     *
     * ["App\Entity\User"=> "User", "App\Entity\Post" => "Post"]
     *
     * in case use union based on property:
     *
     * ["ADMIN"=> "AdminUser", "NORMAL" => "NormalUser"]
     *
     * @var array
     */
    public $discriminatorMap = [];

    /**
     * When use MODE_PROPERTY is the property to get the type
     *
     * @var string
     */
    public $discriminatorProperty;

    /**
     * Name of the class or service to resolve the type
     *
     * @var string
     */
    public $typeResolver;

    /**
     * @return array
     */
    public function getDiscriminatorMap(): array
    {
        return $this->discriminatorMap;
    }

    /**
     * @param array $discriminatorMap
     *
     * @return PolymorphicDefinitionInterface
     */
    public function setDiscriminatorMap(array $discriminatorMap): PolymorphicDefinitionInterface
    {
        $this->discriminatorMap = $discriminatorMap;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscriminatorProperty(): ?string
    {
        return $this->discriminatorProperty;
    }

    /**
     * @param string $discriminatorProperty
     *
     * @return PolymorphicDefinitionInterface
     */
    public function setDiscriminatorProperty(?string $discriminatorProperty): PolymorphicDefinitionInterface
    {
        $this->discriminatorProperty = $discriminatorProperty;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeResolver(): string
    {
        return $this->typeResolver;
    }

    /**
     * @param string $typeResolver
     *
     * @return PolymorphicDefinitionInterface
     */
    public function setTypeResolver(string $typeResolver): PolymorphicDefinitionInterface
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }
}