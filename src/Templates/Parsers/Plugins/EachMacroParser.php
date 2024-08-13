<?php

namespace WebImage\Blocks\Templates\Parsers\Plugins;

use WebImage\Blocks\Templates\Parsers\BranchArgumentDefinition;
use WebImage\Blocks\Templates\Parsers\ParserException;
use WebImage\Blocks\Templates\Parsers\ParserState;

class EachMacroParser extends AbstractMacroParser /* implements TranspilerPluginInterface */
{
//    use TranspilerPluginTrait;

    const T_EACH = 'T_EACH';
    const T_ENDEACH = 'T_ENDEACH';

    protected array $supportedMacros = ['each', 'endEach'];

    protected function getBranchType(string $macroName): string
    {
        switch($macroName) {
            case 'each':
                return parent::getBranchType($macroName);
            case 'endEach':
                return self::T_ENDEACH;
        }

        throw new ParserException('Unknown macro: ' . $macroName);
    }

    public static function getArgumentDefinitions(string $macroName): array
    {
        if ($macroName != 'each') return [];

        return [
            new BranchArgumentDefinition('array', 'The name of the array variable that the control will use to process the loop.'),
            new BranchArgumentDefinition('value', 'The name of the variable that will be made available to reference each array item.'),
            new BranchArgumentDefinition('index', 'The name of the index variable that will be made available to reference each iteration\'s "count."', false),
        ];
    }

    protected function processBody(ParserState $state, string $macroName): array
    {
        if ($macroName != 'each') return [];

        $branches = $state->parser->parseText([self::T_ENDEACH]);
        array_pop($branches); // Don't keep T_ENDEACH
        return $branches;
    }
}
