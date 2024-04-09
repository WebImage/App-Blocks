<?php

namespace WebImage\BlockManager\Templates\Transpilers\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class PhpEachMacroTranspiler extends EachMacroTranspiler
{
	protected function transpileLoop(TranspilerState $state, Branch $branch): string
	{
		list($loopVarName, $loopValueVarName, $loopIndexVarName) = $this->getLoopVarNames($state, $branch);
		$children = $this->transpileChildren($state, $branch);
		$asPair   = $loopIndexVarName === null ? $loopValueVarName : "$loopIndexVarName => $loopValueVarName";
		return $state->getTranspiler()->indent(<<<EOT
<?php foreach(($loopVarName) as $asPair): ?>$children<?php endforeach ?>
EOT, $state->getMeta()['depth'] ? $state->getMeta()['depth'] : 0) . PHP_EOL;
	}
}