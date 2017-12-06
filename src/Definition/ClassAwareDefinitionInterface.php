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
 * Interface ClassAwareDefinitionInterface
 */
interface ClassAwareDefinitionInterface
{
    /**
     * @return mixed
     */
    public function getClass(): ?string;

    /**
     * @param mixed $class
     *
     * @return ClassAwareDefinitionInterface
     */
    public function setClass(?string $class): ClassAwareDefinitionInterface;
}
