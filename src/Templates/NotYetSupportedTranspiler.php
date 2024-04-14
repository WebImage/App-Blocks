<?php

namespace WebImage\BlockManager\src\Templates;

use WebImage\BlockManager\src\Templates\Parsers\Branch;
use WebImage\BlockManager\src\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerState;

trait NotYetSupportedTranspiler
{
    public function transpile(TranspilerState $state, Branch $branch): string
    {
        $line = str_repeat('=-', 6);
        return PHP_EOL . PHP_EOL . $line . ' NOT YET SUPPORTED: ' . $branch->getType() . ($branch->getType() === TemplateParser::T_MACRO ? ': ' . $branch->getValue() : '') . $line . PHP_EOL . PHP_EOL . PHP_EOL;
    }
}
