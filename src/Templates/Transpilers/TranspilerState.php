<?php

namespace WebImage\BlockManager\src\Templates\Transpilers;

use WebImage\BlockManager\src\Templates\Context;
use WebImage\BlockManager\src\Templates\Meta;

class TranspilerState
{
    private TranspilerInterface $transpiler;
    private Meta                $meta;
    private Context             $context;

    /**
     * @param TranspilerInterface $transpiler
     * @param Meta $meta
     * @param Context $context
     */
    public function __construct(TranspilerInterface $transpiler, Meta $meta, Context $context)
    {
        $this->transpiler = $transpiler;
        $this->meta       = $meta;
        $this->context    = $context;
    }

    /**
     * @return \WebImage\BlockManager\src\Templates\Transpilers\TranspilerInterface
     */
    public function getTranspiler(): TranspilerInterface
    {
        return $this->transpiler;
    }

    /**
     * @return Meta
     */
    public function getMeta(): Meta
    {
        return $this->meta;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }
}
