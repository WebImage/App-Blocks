<?php

namespace WebImage\BlockManager\src\Templates;

class Context implements \ArrayAccess
{
    private array $contexts = [];

    public function push(array $initMeta): Meta
    {
        $meta             = new Meta($initMeta);
        $this->contexts[] = $meta;

        return $meta;
    }

    public function pop()
    {
        array_pop($this->contexts);
    }

    public function remove(Meta $context)
    {
        $this->contexts = array_values(array_filter($this->contexts, function (Meta $testContext) use ($context) {
            return $testContext !== $context;
        }));
    }

    /**
     * @param $name
     * @return mixed|void
     * @TODO May need way to reach higher up in the context hierarchy chain to avoid conflicts
     */
    public function offsetExists($offset)
    {
        for ($i = count($this->contexts) - 1; $i >= 0; $i--) {
            if (isset($this->contexts[$i][$offset])) return true;
        }

        return false;
    }

    public function offsetGet($offset)
    {
        for ($i = count($this->contexts) - 1; $i >= 0; $i--) {
            if (isset($this->contexts[$i][$offset])) return $this->contexts[$i][$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        if (count($this->contexts) == 0) $this->push([]);
        $this->contexts[count($this->contexts) - 1][$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException(__METHOD__ . ' not currently supported');
//        unset($this->contexts[count($this->contexts) - 1]);
    }

    public function isSetInCurrentContext($offset): bool
    {
        if (($current = $this->current()) == null) return false;

        return isset($current[$offset]);
    }

    /**
     * Retrieve a list of keys for all levels
     * @return string[]
     */
    public function keys(): array
    {
        $keys = [];
        foreach ($this->keysByLevel() as $levelKeys) {
            $keys = array_merge($keys, $levelKeys);
        }
        $keys = array_unique($keys);
        sort($keys);

        return $keys;
    }

    /**
     * Get a list of keys by context level
     * @return string[][]
     */
    public function keysByLevel(): array
    {
        return array_map(function (Meta $context) {
            return $context->keys();
        }, $this->contexts);
    }

    public function current(): ?Meta
    {
        if (count($this->contexts) == 0) return null;

        return $this->contexts[count($this->contexts) - 1];
    }
}
