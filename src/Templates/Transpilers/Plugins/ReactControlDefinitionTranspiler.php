<?php

namespace WebImage\BlockManager\Templates\Transpilers\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class ReactControlDefinitionTranspiler implements TranspilerPluginInterface
{
	use TranspilerPluginTrait;

	public function canTranspile(TranspilerState $state, Branch $branch): bool
	{
		return ($branch->getType() === TemplateParser::T_MACRO && $branch->getValue() === 'controlDefinition');
	}

	public function transpile(TranspilerState $state, Branch $branch): string
	{
		try {
			$value = $state->getTranspiler()->transpileBranches($branch->getChildren());
		} catch (\Exception $e) {
//			echo '<pre>';print_r($branch); die(__FILE__ . ':' . __LINE__ . PHP_EOL);
			echo 'Error: ' . $e->getMessage() . '<br/>' . PHP_EOL;
			echo '<pre>';print_r($e->getTraceAsString()); die(__FILE__ . ':' . __LINE__ . PHP_EOL);
			echo '<pre>';print_r($branch); die(__FILE__ . ':' . __LINE__ . PHP_EOL);
		}
		return $value;
	}
}