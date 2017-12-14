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

interface ExecutableDefinitionInterface extends
    DefinitionInterface,
    TypeAwareDefinitionInterface,
    ArgumentAwareInterface,
    DeprecateInterface
{
    /**
     * {@inheritDoc}
     */
    public function getResolver():?string;

    /**
     * {@inheritDoc}
     */
    public function setResolver(?string $resolver);
}
