<?php

namespace WebImage\BlockManager\Templates\Parsers\Plugins;

use WebImage\BlockManager\Templates\Meta;
use WebImage\BlockManager\Templates\Parsers\BranchArgumentDefinition;
use WebImage\BlockManager\Templates\Parsers\ParserException;
use WebImage\BlockManager\Templates\Parsers\ParserState;
use WebImage\BlockManager\Templates\Plugins\PropertyMacro;
use WebImage\BlockManager\Templates\Transpilers\Plugins\TranspilerPluginTrait;
use WebImage\BlockManager\Templates\Transpilers\TranspilerPluginInterface;

class ControlMacroParser extends AbstractMacroParser implements TranspilerPluginInterface
{
    use TranspilerPluginTrait;

    const META_CONTROLS = 'controls';
    protected array $supportedMacros = ['control', 'endControl'];

    protected function assertContext(): void
    {
        parent::assertContext();
        if (!isset($this->currentState->meta[ControlsMacroParser::META_CONTROL_GROUP])) {
//			echo '<pre>';print_r($this->currentState->); die(__FILE__ . ':' . __LINE__ . PHP_EOL);
			throw new ParserException('@control must be preceeded by @controls($groupName)');
		}
    }

    protected function processArguments(ParserState $state, array $args)
    {
        if ($this->getMacroName() == 'control') {
            $meta = $this->currentState->meta;
            if (!isset($meta[self::META_CONTROLS])) $meta[self::META_CONTROLS] = new Meta();

            $varName     = static::getArgumentStringByName($this->getMacroName(), $args, 'variableName');
            $controlName = static::getArgumentStringByName($this->getMacroName(), $args, 'controlName');
            $this->assertPropertyExists($state->meta, $varName);

            $meta[self::META_CONTROLS][$varName] = $controlName;
        }
    }

    private function assertPropertyExists(Meta $meta, string $varName)
    {
        if (!isset($meta[PropertyMacro::META_PROPERTIES]) || !isset($meta[PropertyMacro::META_PROPERTIES][$varName])) throw new ParserException('@property(\''. $varName . '\', ...) must be called before @control(\'' . $varName . '\', ...) in order to set up the property');
    }

    public static function getArgumentDefinitions(string $macroName): array
    {
        if ($macroName == 'control') {
            return [
                new BranchArgumentDefinition('variableName', 'The variable name used to reference the control within the template.'),
                new BranchArgumentDefinition('controlName', 'The name of the control type used to manage the variable\'s value.'),
                new BranchArgumentDefinition('label', 'The user friendly name to display when editing this value.', false)
            ];
        }
        return [];
    }
}
