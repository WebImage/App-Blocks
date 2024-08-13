<?php

namespace WebImage\Blocks\Templates\Parsers\Plugins;

use WebImage\Blocks\Templates\Parsers\BranchArgumentDefinition;
use WebImage\Blocks\Templates\Parsers\ParserState;

class BlockMacroParser extends AbstractMacroParser
{
    const MACRO_BLOCK = 'block';
    const MACRO_ENDBLOCK = 'endBlock';
	const MACRO_SUPPORTED_CHILD = 'supportedChild';
	const MACRO_REQUIRED_PARENT = 'requiredParent';

    const META_BLOCK_NAME = 'block.name';
    const META_BLOCK_CLASS = 'block.class';
    const META_BLOCK_LABEL = 'block.label';
	const META_SUPPORTED_CHILDREN = 'block.supportedChildren';
	const META_REQUIRED_PARENTS = 'block.requiredParents';

    const T_END_BLOCK_MACRO = 'T_END_BLOCK_MACRO';

    protected array $supportedMacros = [
		self::MACRO_BLOCK,
		self::MACRO_ENDBLOCK,
		self::MACRO_SUPPORTED_CHILD,
		self::MACRO_REQUIRED_PARENT
	];

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
			case self::MACRO_SUPPORTED_CHILD:
				$meta = $this->currentState->meta;
				$meta[self::META_SUPPORTED_CHILDREN] = ($meta[self::META_SUPPORTED_CHILDREN] ?? []) + [static::getArgumentStringByName($macroName, $args, 'childType')];
				break;
			case self::MACRO_REQUIRED_PARENT:
				$meta[self::META_REQUIRED_PARENTS] = ($meta[self::META_REQUIRED_PARENTS] ?? []) + [static::getArgumentStringByName($macroName, $args, 'parentType')];
				break;
        }
    }

	public static function getArgumentDefinitions(string $macroName): array
    {
		switch ($macroName) {
			case self::MACRO_BLOCK:
				return [
					new BranchArgumentDefinition('name', 'The internal computer-friendly name that will be used to register and refer to this block.'),
					new BranchArgumentDefinition('class', 'The base name for any classes that are generated for rendering this block.'),
					new BranchArgumentDefinition('label', 'The label that is generated for use in any template builders.', false)
				];
				break;
			case self::MACRO_SUPPORTED_CHILD:
				return [
					new BranchArgumentDefinition('childType', 'The name of the child type that can be added to this type (assuming it is a container)', true, true)
				];
				break;
			case self::MACRO_REQUIRED_PARENT:
				return [
					new BranchArgumentDefinition('parentType', 'The name of the parent type that the defined block must be a child of', true, true)
				];
				break;
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
