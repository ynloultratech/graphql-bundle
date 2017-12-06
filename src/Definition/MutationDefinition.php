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
 * Class MutationDefinition
 */
class MutationDefinition extends QueryDefinition
{
    /**
     * @var array
     */
    protected $validationGroups = [];

    /**
     * @return array
     */
    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }

    /**
     * @param array $validationGroups
     *
     * @return MutationDefinition
     */
    public function setValidationGroups(array $validationGroups): MutationDefinition
    {
        $this->validationGroups = $validationGroups;

        return $this;
    }
}
