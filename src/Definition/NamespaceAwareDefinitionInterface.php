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
 * NamespaceAwareDefinitionInterface
 */
interface NamespaceAwareDefinitionInterface
{
    /**
     * @param DefinitionNamespace $namespace
     *
     * @return NamespaceAwareDefinitionInterface
     */
    public function setNamespace(DefinitionNamespace $namespace): NamespaceAwareDefinitionInterface;

    /**
     * @return null|DefinitionNamespace
     */
    public function getNamespace(): ?DefinitionNamespace;
}
