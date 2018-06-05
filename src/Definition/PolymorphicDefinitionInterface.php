<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition;

/**
 * Use this interface on definitions allowing polymorphic object like interfaces and unions
 */
interface PolymorphicDefinitionInterface
{
    /**
     * @return array
     */
    public function getDiscriminatorMap(): array;

    /**
     * @param array $discriminatorMap
     *
     * @return PolymorphicDefinitionInterface
     */
    public function setDiscriminatorMap(array $discriminatorMap): PolymorphicDefinitionInterface;

    /**
     * @return string
     */
    public function getDiscriminatorProperty(): ?string;

    /**
     * @param string $discriminatorProperty
     *
     * @return PolymorphicDefinitionInterface
     */
    public function setDiscriminatorProperty(?string $discriminatorProperty): PolymorphicDefinitionInterface;

    /**
     * @return string
     */
    public function getTypeResolver(): string;

    /**
     * @param string $typeResolver
     *
     * @return PolymorphicDefinitionInterface
     */
    public function setTypeResolver(string $typeResolver): PolymorphicDefinitionInterface;
}
