<?php

namespace WebImage\Blocks\Templates\Plugins;

use WebImage\Blocks\Templates\Parsers\BranchArgumentDefinition;
use WebImage\Blocks\Templates\Parsers\ParserState;
use WebImage\Blocks\Templates\Parsers\Plugins\AbstractMacroParser;

class ControlDefinitionMacro extends AbstractMacroParser
{
//    const T_CONTROL_DEFINITION = 'controlDefinition';
    const T_END_CONTROL_DEFINITION = 'T_END_CONTROL_DEFINITION';

    const MACRO_CONTROL_DEFINITION = 'controlDefinition';
    const MACRO_END_CONTROL_DEFINITION = 'endControlDefinition';

    protected array $supportedMacros = [self::MACRO_CONTROL_DEFINITION, self::MACRO_END_CONTROL_DEFINITION];

    protected function getBranchType(string $macroName): string
    {
        switch ($macroName) {
            case self::MACRO_END_CONTROL_DEFINITION:
                return self::T_END_CONTROL_DEFINITION;
            default:
                return parent::getBranchType($macroName);
        }
    }

    protected function processBody(ParserState $state, string $macroName): array
    {
        switch($macroName) {
            case self::MACRO_CONTROL_DEFINITION:
                $body = $state->parser->parseText([self::T_END_CONTROL_DEFINITION]);
                array_pop($body); // Do not keep T_END_CONTROL_DEFINITION
                return $body;
            default:
                return [];
        }
    }

	public static function getArgumentDefinitions(string $macroName): array
    {
        if ($macroName == self::MACRO_CONTROL_DEFINITION) {
            return [
                new BranchArgumentDefinition('name', 'Makes any template code following the @controlDefintion available as a control of this nae.'),
                new BranchArgumentDefinition('label', 'The user friendly name for the control.')
            ];
        }

        return [];
    }
}
