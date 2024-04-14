<?php

namespace WebImage\BlockManager\src\Templates\Transpilers\Plugins;

use WebImage\BlockManager\src\Templates\Parsers\Branch;
use WebImage\BlockManager\src\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerState;

class EachMacroTranspiler implements TranspilerPluginInterface
{
	use TranspilerPluginTrait;

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_MACRO && $branch->getValue() == 'each';
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
		return $this->transpileLoop($state, $branch);
    }

	protected function transpileChildren(TranspilerState $state, Branch $branch): string
	{
		return $state->getTranspiler()->transpileBranches($branch->getChildren());
	}

	protected function transpileLoop(TranspilerState $state, Branch $branch): string
	{
		return '';
	}

	/**
	 * Returns the three variables use in the loop (loopVarName, loopItemVarName, loopIndexVarName)
	 * @param TranspilerState $state
	 * @param Branch $branch
	 * @return array
	 */
	protected function getLoopVarNames(TranspilerState $state, Branch $branch): array
	{
		return [
			$this->getLoopVarName($state, $branch),
			$this->getLoopItemVarName($state, $branch),
			$this->getLoopIndexVarName($state, $branch)
		];
	}

	protected function getLoopVarName(TranspilerState $state, Branch $branch): string
	{
		$args    = $branch->getArgs();
		$nameBranch = new Branch($args[0]->getType(), $args[0]->getValue(), $args[0]->getChildren(), $args[0]->getArgs(), array_merge($args[0]->getMeta()->toArray(), ['default' => new Branch(TemplateParser::T_LITERAL, '[]')]));
//        $args[1] = new Branch($args[1]->getType(), $args[1]->getValue(), $args[1]->getChildren(), $args[1]->getArgs(), array_merge($args[1]->getMeta()->toArray(), [TemplateParser::META_SAFETY_CHECKS => false]));
//
		return $state->getTranspiler()->transpile($nameBranch);
	}

	protected function getLoopItemVarName(TranspilerState $state, Branch $branch): string
	{
		$args    = $branch->getArgs();
		$itemNameBranch = new Branch($args[1]->getType(), $args[1]->getValue(), $args[1]->getChildren(), $args[1]->getArgs(), array_merge($args[1]->getMeta()->toArray(), [TemplateParser::META_SAFETY_CHECKS => false]));

		return $state->getTranspiler()->transpile($itemNameBranch);
	}

	protected function getLoopIndexVarName(TranspilerState $state, Branch $branch): ?string
	{
		$args    = $branch->getArgs();

		return isset($args[2]) ? $state->getTranspiler()->transpile($args[2]) : null;
	}
}