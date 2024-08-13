<?php

namespace WebImage\Blocks\Templates\Transpilers\Plugins;

use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Transpilers\TranspilerState;

class PhpBlockMacroTranspiler extends BlockMacroTranspiler
{
    protected function getBlockCommentHeader(TranspilerState $state, Branch $branch): string
    {
        return '<?php ' . PHP_EOL;
    }

    protected function getBlockCommentFooter(TranspilerState $state, Branch $branch): string
    {
        return ' ?>';
    }


}
