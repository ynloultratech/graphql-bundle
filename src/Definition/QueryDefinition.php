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
 * Class QueryDefinition
 */
class QueryDefinition implements DeprecateInterface, ArgumentAwareInterface
{
    use DeprecateTrait;
    use ArgumentAwareTrait;

    protected $name;

    protected $type;

    protected $list = false;

    protected $resolver;

    protected $description;

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isList(): bool
    {
        return $this->list;
    }

    /**
     * {@inheritDoc}
     */
    public function setList(bool $list)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getResolver():?string
    {
        return $this->resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function setResolver(?string $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription():?string
    {
        return $this->description;
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }
}
