<?php

namespace WebImage\BlockManager\Templates;

class Meta implements \Countable, \ArrayAccess, \Iterator
{
    private array $_values = [];

    public function __construct(array $init = [])
    {
        foreach ($init as $key => $val) {
            $this->_values[$key] = $val;
        }
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->_values);
    }

    public function offsetGet($offset)
    {
        return $this->_values[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->_values[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_values[$offset]);
    }

    public function count()
    {
        return count($this->_values);
    }

    public function current()
    {
        return current($this->_values);
    }

    public function next()
    {
        next($this->_values);
    }

    public function key()
    {
        return key($this->_values);
    }

    public function valid()
    {
        return ($this->key() !== null);
    }

    public function rewind()
    {
        reset($this->_values);
    }

    public function merge(Meta $meta): void
    {
        foreach ($meta as $key => $val) {
            $this->_values[$key] = $val;
        }
    }

    public function toArray(): array
    {
        return $this->_values;
    }

    public function keys(): array
    {
        return array_keys($this->_values);
    }
}
