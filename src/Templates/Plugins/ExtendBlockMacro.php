<?php

namespace WebImage\Blocks\Templates\Plugins;

use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Parsers\BranchArgumentDefinition;
use WebImage\Blocks\Templates\Parsers\ParserState;
use WebImage\Blocks\Templates\Parsers\Plugins\AbstractMacroParser;
use WebImage\Blocks\Templates\Parsers\TemplateParser;
use WebImage\Blocks\Templates\Transpilers\Plugins\TranspilerPluginTrait;
use WebImage\Blocks\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\Blocks\Templates\Transpilers\TranspilerState;

class ExtendBlockMacro extends AbstractMacroParser implements TranspilerPluginInterface
{
    use TranspilerPluginTrait;

    const META_BLOCK_EXTEND = 'block.extend';
    const MACRO_EXTEND = 'extend';

    protected array $supportedMacros = [self::MACRO_EXTEND];

    public static function getArgumentDefinitions(string $macroName): array
    {
        return [
            new BranchArgumentDefinition('blockName', 'The name of the block that is being extended.')
        ];
    }

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_MACRO && $branch->getValue() == self::MACRO_EXTEND;
    }

    protected function createBranch(ParserState $state, string $type, string $name, array $children = [], array $args = [], array $meta = []): ?Branch
    {
        $name = self::getArgumentStringByName($this->getMacroName(), $args, 'blockName');
        $state->meta[self::META_BLOCK_EXTEND] = $name;

        return null;
    }
}
