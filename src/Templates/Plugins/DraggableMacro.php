<?php

namespace WebImage\BlockManager\Templates\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\ParserState;
use WebImage\BlockManager\Templates\Parsers\Plugins\AbstractMacroParser;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\Plugins\TranspilerPluginTrait;
use WebImage\BlockManager\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class DraggableMacro extends AbstractMacroParser implements TranspilerPluginInterface
{
    use TranspilerPluginTrait;

    const META_DRAGGABLE = 'block.draggable';

    protected array $supportedMacros = ['draggable'];

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_MACRO && $branch->getValue() == 'draggable';
    }

    /**
     * Set meta for block and disregard branch
     * @param ParserState $state
     * @param string $type
     * @param string $name
     * @param array $children
     * @param array $args
     * @return Branch|null
     */
    protected function createBranch(ParserState $state, string $type, string $name, array $children = [], array $args = [], array $meta = []): ?Branch
    {
        $state->meta[self::META_DRAGGABLE] = true;

        return null;
    }
}
