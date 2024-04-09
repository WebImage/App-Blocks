<?php

namespace WebImage\BlockManager\Templates\Transpilers\Plugins;

use WebImage\BlockManager\Templates\Transpilers\BranchTranspilerInterface;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

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
