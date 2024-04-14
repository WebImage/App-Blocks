<?php

namespace WebImage\BlockManager\src\Templates\Transpilers\Plugins;

use WebImage\BlockManager\src\Templates\Parsers\Branch;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerState;

trait TranspilerPluginTrait
{
    public function canPreProcess(TranspilerState $state, Branch $root): bool
    {
        return false;
    }

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return false;
    }

    public function preProcess(TranspilerState $state, Branch $root): Branch {
        return $root;
    }

    public function transpile(TranspilerState $state, Branch $branch): string {
        return '';
    }
}
