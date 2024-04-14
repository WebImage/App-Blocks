<?php

namespace WebImage\BlockManager\src\Templates\Parsers\Plugins;

use WebImage\BlockManager\src\Templates\Parsers\BranchArgumentDefinition;
use WebImage\BlockManager\src\Templates\Parsers\ParserState;

class BlockMacroParser extends AbstractMacroParser
{
    const MACRO_BLOCK = 'block';
    const MACRO_ENDBLOCK = 'endBlock';
    const META_BLOCK_NAME = 'block.name';
    const META_BLOCK_CLASS = 'block.class';
    const META_BLOCK_LABEL = 'block.label';
    const T_END_BLOCK_MACRO = 'T_END_BLOCK_MACRO';

    protected array $supportedMacros = [self::MACRO_BLOCK, self::MACRO_ENDBLOCK];

    protected function processArguments(ParserState $state, array $args)
    {
        $macroName = $this->getMacroName();

        switch ($macroName) {
            case self::MACRO_BLOCK:
                $meta                         = $this->currentState->meta;
                $meta[self::META_BLOCK_NAME]  = static::getArgumentStringByName($macroName, $args, 'name');
                $meta[self::META_BLOCK_CLASS] = static::getArgumentStringByName($macroName, $args, 'class');
                $meta[self::META_BLOCK_LABEL] = static::getArgumentStringByName($macroName, $args, 'label');
                break;
        }
    }

    public static function getArgumentDefinitions(string $macroName): array
    {
        if ($macroName == self::MACRO_BLOCK) {
            return [
                new BranchArgumentDefinition('name', 'The internal computer-friendly name that will be used to register and refer to this block.'),
                new BranchArgumentDefinition('class', 'The base name for any classes that are generated for rendering this block.'),
                new BranchArgumentDefinition('label', 'The label that is generated for use in any template builders.', false)
            ];
        }

        return [];
    }

    protected function getBranchType(string $macroName): string
    {
        if ($macroName === self::MACRO_ENDBLOCK) return self::T_END_BLOCK_MACRO;

        return parent::getBranchType($macroName);
    }

    protected function processBody(ParserState $state, string $macroName): array
    {
        if ($macroName === self::MACRO_BLOCK) {
            $body = $state->parser->parseText([self::T_END_BLOCK_MACRO, null]);
			// No point in keeping T_END_BLOCK_MACRO
			if (count($body) > 0 && $body[count($body) - 1]->getType() == self::T_END_BLOCK_MACRO) {
				array_pop($body);
			}

			return $body;
        }

        return parent::processBody($state, $macroName);
    }
}
