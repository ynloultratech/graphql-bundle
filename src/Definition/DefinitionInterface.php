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
 * Interface DefinitionInterface
 */
interface DefinitionInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return DefinitionInterface
     */
    public function setName(?string $name): DefinitionInterface;

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return DefinitionInterface
     */
    public function setDescription(?string $description): DefinitionInterface;
}
