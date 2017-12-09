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

use Ynlo\GraphQLBundle\Definition\ArgumentAwareInterface;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;

/**
 * Trait ArgumentAwareTrait
 */
trait ArgumentAwareTrait
{
    /**
     * @var ArgumentDefinition[]
     */
    protected $arguments = [];

    /**
     * @return ArgumentDefinition[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param ArgumentDefinition[] $arguments
     *
     * @return ArgumentAwareInterface
     */
    public function setArguments(array $arguments): ArgumentAwareInterface
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return ArgumentDefinition
     */
    public function getArgument(string $name): ArgumentDefinition
    {
        return $this->arguments[$name];
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }

    /**
     * @param ArgumentDefinition $argument
     *
     * @return ArgumentAwareInterface
     */
    public function addArgument(ArgumentDefinition $argument): ArgumentAwareInterface
    {
        $this->arguments[$argument->getName()] = $argument;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return ArgumentAwareInterface
     */
    public function removeArgument(string $name): ArgumentAwareInterface
    {
        unset($this->arguments[$name]);

        return $this;
    }
}
