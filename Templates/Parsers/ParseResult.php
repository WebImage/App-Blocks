<?php

namespace WebImage\BlockManager\Templates\Parsers;

use WebImage\BlockManager\Templates\Meta;
use WebImage\BlockManager\Templates\Parsers\Branch;

class ParseResult
{
    private Branch $root;
    private Meta  $meta;

    /**
     * @param array $results
     * @param Meta $meta
     */
    public function __construct(Branch $root, Meta $meta)
    {
        $this->root = $root;
        $this->meta = $meta;
    }

    /**
     * @return array
     */
    public function getRoot(): Branch
    {
        return $this->root;
    }

    /**
     * @return Meta
     */
    public function getMeta(): Meta
    {
        return $this->meta;
    }
}
