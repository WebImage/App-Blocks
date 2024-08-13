<?php

namespace WebImage\Blocks\Templates\Parsers\Plugins;

use WebImage\Blocks\Templates\Meta;
use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Parsers\BranchArgumentDefinition;
use WebImage\Blocks\Templates\Parsers\ParserState;

class ControlsMacroParser extends AbstractMacroParser
{
//    const CONTEXT_CONTROL_GROUP = 'control.group';
    const META_CONTROL_GROUP = 'control.currentGroup';

    protected array $supportedMacros = ['controls'];

//    protected function createContext(): array
//    {
//        $args = $this->currentState->context[self::CONTEXT_MACRO_ARGS];
//        $name = self::getArgumentStringByName($this->getMacroName(), $args, 'name');
//
//		return [
//			self::META_CONTROL_GROUP => $name
//		];
////        $this->currentState->meta[self::META_CONTROL_GROUP] = $name;
////
////        return null;
//    }
	protected function processArguments(ParserState $state, array $args)
	{
		$state->meta[self::META_CONTROL_GROUP] = self::getArgumentStringByName($this->getMacroName(), $args, 'groupName');
	}


	protected function createBranch(ParserState $state, string $type, string $name, array $children = [], array $args = [], array $meta = []): ?Branch
    {
        return null;
    }

    public static function getArgumentDefinitions(string $macroName): array
    {
        return [
            new BranchArgumentDefinition('groupName', 'The machine-friendly name under which to group this control, e.g. "general")')
        ];
    }
}
