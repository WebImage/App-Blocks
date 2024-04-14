<?php

namespace WebImage\BlockManager\src\Templates;

class Meta implements \Countable, \ArrayAccess, \Iterator
{
    private array $meta = [];

    public function __construct(array $init = [])
    {
        foreach ($init as $key => $val) {
            $this->meta[$key] = $val;
        }
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->meta);
    }

    public function offsetGet($offset)
    {
        return $this->meta[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->meta[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->meta[$offset]);
    }

    public function count()
    {
        return count($this->meta);
    }

    public function current()
    {
        return current($this->meta);
    }

    public function next()
    {
        next($this->meta);
    }

    public function key()
    {
        return key($this->meta);
    }

    public function valid()
    {
        return ($this->key() !== null);
    }

    public function rewind()
    {
        reset($this->meta);
    }

    public function merge(Meta $meta): void
    {
        foreach ($meta as $key => $val) {
            $this->meta[$key] = $val;
        }
    }

    public function toArray(): array
    {
        return $this->meta;
    }

    public function keys(): array
    {
        return array_keys($this->meta);
    }
}
