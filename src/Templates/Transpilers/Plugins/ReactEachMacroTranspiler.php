<?php

namespace WebImage\Blocks\Templates\Transpilers\Plugins;

use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Transpilers\TranspilerState;

class ReactEachMacroTranspiler extends EachMacroTranspiler
{
	protected function transpileLoop(TranspilerState $state, Branch $branch): string
	{
		list($loopVarName, $loopValueVarName, $loopIndexVarName) = $this->getLoopVarNames($state, $branch);
		$children = $this->transpileChildren($state, $branch);

	 	$arguments = $loopIndexVarName === null ? $loopValueVarName : "$loopValueVarName, $loopIndexVarName";

		return $state->getTranspiler()->indent(<<<EOT
{($loopVarName).map(($arguments) => {
return (
<React.Fragment>
$children
</React.Fragment>
);
})}
EOT, $state->getMeta()['depth'] ? $state->getMeta()['depth'] : 0);
	}
}