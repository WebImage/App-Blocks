<?php

namespace WebImage\Blocks\Templates\Transpilers;

use WebImage\Blocks\Templates\Parsers\Branch;

interface VariableInterface
{
    public function renderVariable(Branch $branch): string;
    public function formatVariable(string $varName): string;
}
