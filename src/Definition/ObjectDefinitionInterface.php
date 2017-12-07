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
 * Interface ObjectDefinitionInterface
 */
interface ObjectDefinitionInterface extends
    DefinitionInterface,
    ClassAwareDefinitionInterface,
    FieldsAwareDefinitionInterface
{
    public const EXCLUDE_ALL = 'ALL';
    public const EXCLUDE_NONE = 'NONE';

    /**
     * @return string
     */
    public function getExclusionPolicy(): string;

    /**
     * @param string $exclusionPolicy
     */
    public function setExclusionPolicy(string $exclusionPolicy);
}
