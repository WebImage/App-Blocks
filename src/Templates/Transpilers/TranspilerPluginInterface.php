<?php

namespace WebImage\BlockManager\Templates\Transpilers;

use WebImage\BlockManager\Templates\Parsers\Branch;

interface TranspilerPluginInterface
{
    public function canPreProcess(TranspilerState $state, Branch $root): bool;
    public function preProcess(TranspilerState $state, Branch $root): Branch;
    public function canTranspile(TranspilerState $state, Branch $branch): bool;

    public function transpile(TranspilerState $state, Branch $branch): string;
}
