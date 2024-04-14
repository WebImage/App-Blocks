<?php

namespace WebImage\BlockManager\Templates\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\Plugins\AbstractTranspilerPlugin;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class PhpCode extends AbstractTranspilerPlugin
{
    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_CODE;
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
        $renderedChildren = array_map(function(Branch $child) use ($state) {
            return $state->getTranspiler()->transpile($child);
        }, $branch->getChildren());

        return '<?php echo ' . implode(' . ', $renderedChildren) . ' ?>';
    }
}
