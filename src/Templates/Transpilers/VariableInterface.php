<?php

namespace WebImage\BlockManager\src\Templates\Transpilers;

use WebImage\BlockManager\src\Templates\Parsers\Branch;

interface VariableInterface
{
    public function renderVariable(Branch $branch): string;
    public function formatVariable(string $varName): string;
}
