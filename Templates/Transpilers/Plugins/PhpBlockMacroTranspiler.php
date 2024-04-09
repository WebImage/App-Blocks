<?php

namespace WebImage\BlockManager\Templates\Transpilers\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

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
