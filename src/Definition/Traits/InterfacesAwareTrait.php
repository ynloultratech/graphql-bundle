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

/**
 * InterfacesAwareTrait
 */
trait InterfacesAwareTrait
{
    /**
     * @var string[]
     */
    protected $interfaces = [];

    /**
     * @return string[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @param string $name
     */
    public function addInterface(string $name)
    {
        $this->interfaces[] = $name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function removeInterface(string $name)
    {
        foreach ($this->interfaces as $index => $interface) {
            if ($interface === $name) {
                unset($this->interfaces[$index]);
                break;
            }
        }

        return $this;
    }
}
