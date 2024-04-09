<?php

namespace WebImage\BlockManager\Templates\Transpilers;

use WebImage\BlockManager\Templates\Parsers\Branch;

interface VariableInterface
{
    public function renderVariable(Branch $branch): string;
    public function formatVariable(string $varName): string;
}
