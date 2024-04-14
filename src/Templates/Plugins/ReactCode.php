<?php

namespace WebImage\BlockManager\Templates\Plugins;

use Exception;
use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\Plugins\AbstractTranspilerPlugin;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class ReactCode extends AbstractTranspilerPlugin
{
    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return in_array($branch->getType(), [TemplateParser::T_CODE, TemplateParser::T_OBJECT, TemplateParser::T_KEY_VALUE_PAIR]);
    }

	/**
	 * @throws Exception
	 */
	public function transpile(TranspilerState $state, Branch $branch): string
    {
		switch($branch->getType()) {
			case TemplateParser::T_CODE:
				return $this->transpileCode($state, $branch);
			case TemplateParser::T_OBJECT:
				return $this->transpileObject($state, $branch);
			case TemplateParser::T_KEY_VALUE_PAIR:
				return $this->transpileKeyPair($state, $branch);
			default:
				throw new Exception('Unknown branch type: ' . $branch->getType());
		}
    }

	/**
	 * Transpiles a code block
	 */
	private function transpileCode(TranspilerState $state, Branch $branch): string
	{
		$renderedChildren = array_map(function(Branch $child) use ($state) {
			return $state->getTranspiler()->transpile($child);
		}, $branch->getChildren());

		return '{' . implode(' + ', $renderedChildren) . '}';
	}

	private function transpileObject(TranspilerState $state, Branch $branch): string
	{
		$renderedChildren = array_map(function(Branch $child) use ($state) {
			return $state->getTranspiler()->transpile($child);
		}, $branch->getChildren());

		return '{' . implode(', ', $renderedChildren) . '}';
	}

	private function transpileKeyPair(TranspilerState $state, Branch $branch): string
	{
		$keyBranch = new Branch(TemplateParser::T_STRING, $branch->getValue(), [], $branch->getArgs(), $branch->getMeta()->toArray());
		$value = $state->getTranspiler()->transpileBranches($branch->getChildren());

		return sprintf('%s: %s', $state->getTranspiler()->transpile($keyBranch), $value);
	}
}
