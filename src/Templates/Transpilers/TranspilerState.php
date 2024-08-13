<?php

namespace WebImage\Blocks\Templates\Transpilers;

use WebImage\Blocks\Templates\Context;
use WebImage\Blocks\Templates\Meta;

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
     * @return \WebImage\Blocks\Templates\Transpilers\TranspilerInterface
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
