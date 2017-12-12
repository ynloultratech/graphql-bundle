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

use Ynlo\GraphQLBundle\Definition\DefinitionNamespace;
use Ynlo\GraphQLBundle\Definition\NamespaceAwareDefinitionInterface;

/**
 * NamespaceAwareTrait
 */
trait NamespaceAwareTrait
{
    /**
     * @var DefinitionNamespace
     */
    protected $namespace;

    /**
     * @param DefinitionNamespace $namespace
     *
     * @return NamespaceAwareDefinitionInterface
     */
    public function setNamespace(DefinitionNamespace $namespace): NamespaceAwareDefinitionInterface
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return null|DefinitionNamespace
     */
    public function getNamespace(): ?DefinitionNamespace
    {
        return $this->namespace;
    }
}
