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
 * Trait ClassAwareDefinitionTrait
 */
trait ClassAwareDefinitionTrait
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @return string
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return ClassAwareDefinitionInterface
     */
    public function setClass(?string $class): ClassAwareDefinitionInterface
    {
        $this->class = $class;

        return $this;
    }
}
