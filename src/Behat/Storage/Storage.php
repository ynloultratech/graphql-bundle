<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Storage;

/**
 * Store tmp values to use during tests
 */
class Storage implements \ArrayAccess, \Iterator
{
    protected $data = [];

    /**
     * Storage constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): Storage
    {
        $this->data = $data;

        return $this;
    }

    public function clear()
    {
        $this->setData([]);
    }

    public function setValue(string $key, $value): bool
    {
        $this->data[$key] = $value;

        return isset($this->data[$key]);
    }

    public function getValue(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getValue($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->setValue($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->data);
    }

    #[\ReturnTypeWillChange]
    public function next(): void
    {
        next($this->data);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->data);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    /**
     *
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getValue($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        return $this->setValue($name, $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        return $this->offsetUnset($name);
    }
}
