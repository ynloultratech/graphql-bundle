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

use Ynlo\GraphQLBundle\Definition\Traits\ClassAwareDefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\DefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\FieldsAwareDefinitionTrait;
use Ynlo\GraphQLBundle\Definition\Traits\ObjectDefinitionTrait;

/**
 * Class ObjectDefinition
 */
class ObjectDefinition implements ObjectDefinitionInterface, NodeAwareDefinitionInterface
{
    use DefinitionTrait;
    use FieldsAwareDefinitionTrait;
    use ClassAwareDefinitionTrait;
    use ObjectDefinitionTrait;

    /**
     * @var string[]
     */
    protected $extensions = [];

    /**
     * @var InterfaceExtensionDefinition[]
     */
    protected $interfaces = [];

    /**
     * @return string[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @param string $name
     */
    public function addInterface(string $name)
    {
        $this->interfaces[] = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function setNode(?string $node): NodeAwareDefinitionInterface
    {
        $this->setClass($node);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getNode(): ?string
    {
        return $this->getClass();
    }


    /**
     * @return InterfaceExtensionDefinition[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @param string $class
     * @param int    $priority
     */
    public function addExtension($class, $priority = 0)
    {
        $this->extensions[$class] = new InterfaceExtensionDefinition($class, $priority);
    }
}
