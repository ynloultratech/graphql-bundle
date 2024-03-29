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

use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;

/**
 * Trait ObjectDefinitionTrait
 */
trait ObjectDefinitionTrait
{
    /**
     * @var string
     */
    protected $exclusionPolicy = ObjectDefinitionInterface::EXCLUDE_NONE;

    /**
     * @return string
     */
    public function getExclusionPolicy(): string
    {
        return $this->exclusionPolicy;
    }

    /**
     * @param string $exclusionPolicy
     */
    public function setExclusionPolicy(string $exclusionPolicy)
    {
        $this->exclusionPolicy = $exclusionPolicy;
    }
}
