<?php

namespace WebImage\BlockManager\Templates\Parsers\Plugins;

use WebImage\BlockManager\Templates\Meta;
use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\BranchArgumentDefinition;
use WebImage\BlockManager\Templates\Parsers\ParserState;

class ControlsMacroParser extends AbstractMacroParser
{
//    const CONTEXT_CONTROL_GROUP = 'control.group';
    const META_CONTROL_GROUP = 'control.currentGroup';

    protected array $supportedMacros = ['controls'];

    protected function startContext(): ?Meta
    {
        $args = $this->currentState->context[self::CONTEXT_MACRO_ARGS];
        $name = self::getArgumentStringByName($this->getMacroName(), $args, 'name');

        $this->currentState->meta[self::META_CONTROL_GROUP] = $name;

        return null;
    }

    protected function createBranch(ParserState $state, string $type, string $name, array $children = [], array $args = [], array $meta = []): ?Branch
    {
        return null;
    }

    public static function getArgumentDefinitions(string $macroName): array
    {
        return [
            new BranchArgumentDefinition('name', 'The machine-friendly name under which to group this control, e.g. "general")')
        ];
    }
}
