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

use Ynlo\GraphQLBundle\Definition\DeprecateInterface;

/**
 * Trait DeprecateTrait
 */
trait DeprecateTrait
{
    /**
     * @var string
     */
    protected $deprecationReason;

    /**
     * @param string $deprecationReason
     *
     * @return DeprecateInterface
     */
    public function setDeprecationReason(?string $deprecationReason): DeprecateInterface
    {
        $this->deprecationReason = $deprecationReason;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return (bool) $this->getDeprecationReason();
    }

    /**
     * @return string
     */
    public function getDeprecationReason():?string
    {
        return $this->deprecationReason;
    }
}
