<?php

namespace WebImage\Blocks\Templates\Parsers;

use WebImage\Blocks\Templates\Meta;

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
	 * @return Branch
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
