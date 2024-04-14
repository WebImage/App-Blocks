<?php

namespace WebImage\BlockManager\src\Templates\Transpilers\Plugins;

use WebImage\BlockManager\src\Templates\Parsers\Branch;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerState;

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
