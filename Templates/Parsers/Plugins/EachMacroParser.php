<?php

namespace WebImage\BlockManager\Templates\Parsers\Plugins;

use WebImage\BlockManager\Templates\Lexers\TemplateLexerDebugger;
use WebImage\BlockManager\Templates\Parsers\ParserException;
use WebImage\BlockManager\Templates\Parsers\ParserState;
use WebImage\BlockManager\Templates\Transpilers\TranspilerInterface;
use WebImage\BlockManager\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\BranchArgumentDefinition;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\Plugins\TranspilerPluginTrait;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

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
