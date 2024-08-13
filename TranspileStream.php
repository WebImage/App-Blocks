<?php

namespace WebImage\Blocks;

use WebImage\Blocks\Templates\Parsers\ParseResult;
use WebImage\Blocks\Templates\Transpilers\Transpiler;

class TranspileStream
{
    private Transpiler $transpiler;

    /**
     * @param Transpiler $transpiler
     * @param string $outDir
     */
    public function __construct(Transpiler $transpiler)
    {
        $this->transpiler = $transpiler;
    }

    public function __invoke(ParseResult $result)
    {
        echo gettype($result) . '<br/>' . PHP_EOL;
		// $stream = new TranspileStream($reactTranspiler)
		// $stream($result);
        return null;
        return $this->transpiler->transpile($result->getRoot(), $result->getMeta());
    }
}
