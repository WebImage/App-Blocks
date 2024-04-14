<?php

namespace WebImage\BlockManager\src\Templates\Transpilers\Plugins;

use WebImage\BlockManager\src\Templates\Transpilers\TranspilerState;
use WebImage\BlockManager\Templates\Transpilers\BranchTranspilerInterface;

class ReactControlBranchTranspiler implements BranchTranspilerInterface
{
    public function canTranspileBranch(TranspilerState $state, array $branch): bool
    {
        die(__FILE__ . ':' . __LINE__ . '<br />' . PHP_EOL);
    }

    public function transpileBranch(TranspilerState $state, array $branch): string
    {
        // TODO: Implement renderBranch() method.
    }
}
