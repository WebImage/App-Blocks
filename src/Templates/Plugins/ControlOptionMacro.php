<?php

namespace WebImage\BlockManager\Templates\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\BranchArgumentDefinition;
use WebImage\BlockManager\Templates\Parsers\Plugins\AbstractMacroParser;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\Plugins\TranspilerPluginTrait;
use WebImage\BlockManager\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class ControlOptionMacro extends AbstractMacroParser implements TranspilerPluginInterface
{
    const MACRO_CONTROL_OPTION = 'controlOption';

    use TranspilerPluginTrait;

    protected array $supportedMacros = [self::MACRO_CONTROL_OPTION];

    public static function getArgumentDefinitions(string $macroName): array
    {
        return [
            new BranchArgumentDefinition('variableName', 'The variable name used within the template and as specified by @control(\'variable-name\', ...) within the same block.'),
            new BranchArgumentDefinition('controlOptionName', 'The name of the control\'s option being modified.'),
            new BranchArgumentDefinition('controlOptionValue', 'The value to be passed to the specified control option.', false)
//            new BranchArgumentDefinition('controlOptionDefault', '', false)
        ];
    }

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_MACRO && $branch->getValue() == self::MACRO_CONTROL_OPTION;
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
        return '';
    }
}
