<?php

namespace WebImage\BlockManager\Templates;

use WebImage\BlockManager\Templates\Meta;

class ImmutableMeta extends Meta
{
    public function offsetSet($offset, $value)
    {
        throw new \InvalidArgumentException('Cannot set value on ImmutableMeta');
    }
}
