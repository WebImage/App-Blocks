<?php

namespace WebImage\Blocks\Templates;

class ImmutableMeta extends Meta
{
    public function offsetSet($offset, $value)
    {
        throw new \InvalidArgumentException('Cannot set value on ImmutableMeta');
    }
}
