<?php

namespace WebImage\BlockManager\src\Templates\Parsers\Plugins;

use WebImage\BlockManager\src\Templates\NotYetSupportedTranspiler;
use WebImage\BlockManager\src\Templates\Parsers\Branch;
use WebImage\BlockManager\src\Templates\Parsers\BranchArgumentDefinition;
use WebImage\BlockManager\src\Templates\Parsers\ParserState;
use WebImage\BlockManager\src\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\src\Templates\Transpilers\Plugins\TranspilerPluginTrait;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerState;

class WrapMacroParser extends AbstractMacroParser implements TranspilerPluginInterface
{
    const META_BLOCK_WRAP = 'block.wrap';

    use TranspilerPluginTrait;
    use NotYetSupportedTranspiler {
        NotYetSupportedTranspiler::transpile insteadof TranspilerPluginTrait;
    }
    protected array $supportedMacros = ['wrap'];

    /**
     * Sets block.wraps meta data for block and disregard branch
     * @param ParserState $state
     * @param string $type
     * @param string $name
     * @param array $children
     * @param array $args
     * @return Branch|null
     */
    protected function createBranch(ParserState $state, string $type, string $name, array $children = [], array $args = [], array $meta = []): ?Branch
    {
        $name = self::getArgumentStringByName($this->getMacroName(), $args, 'name');

        $state->meta[self::META_BLOCK_WRAP] = array_merge($state->meta[self::META_BLOCK_WRAP] ?? [], [$name]);

        return null;
    }

    public static function getArgumentDefinitions(string $macroName): array
    {
        return [
            new BranchArgumentDefinition('name', 'Wraps the template in another block by specifying the block name.  The block will then be injected into a "child" variable that will become available in the parent block.')
        ];
    }

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_MACRO && $branch->getValue() == 'wrap';
    }
}
