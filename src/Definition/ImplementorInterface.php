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
 * Must be implemented on types capable of have interfaces
 */
interface ImplementorInterface extends DefinitionInterface, FieldsAwareDefinitionInterface
{
    /**
     * @return string[]
     */
    public function getInterfaces(): array;

    /**
     * @param string $name
     */
    public function addInterface(string $name);

    /**
     * @param string $name
     */
    public function removeInterface(string $name);
}
