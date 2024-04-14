<?php

namespace WebImage\BlockManager\Processing;

use WebImage\BlockManager\Templates\IndentHelper;
use WebImage\BlockManager\Templates\Parsers\Plugins\BlockMacroParser;
use WebImage\BlockManager\Templates\Plugins\PropertyMacro;

class ReactBlockTypeGenerator implements ProcessorInterface
{
	/**
	 * @param BlockFile[] $blockFiles
	 * @return void
	 */
	public function process(array $blockFiles): void
	{
		$indent = '  ';

		foreach($blockFiles as $blockFile) {
			$meta = $blockFile->getResult()->getMeta();

			// Only process properties if they are associated with a block
			if (!isset($meta[BlockMacroParser::META_BLOCK_NAME])) continue;

			$blockName = $meta[BlockMacroParser::META_BLOCK_NAME];
			$className = $meta[BlockMacroParser::META_BLOCK_CLASS];
			$typeClassName = sprintf('%sType', $className);

			$props = $meta[PropertyMacro::META_PROPERTIES] ?? [];

			$output = 'type ' . $typeClassName . ' {' . PHP_EOL;

			foreach($props as $prop => $propDef) {
				$type = ReactVariableTypes::getReactType($propDef['type']);

				$output .= IndentHelper::indent(sprintf('%s: %s', $prop, $type), $indent, 2) . PHP_EOL;
			}

			$output .= '}' . PHP_EOL;
			$output .= PHP_EOL;
			$output .= 'export default ' . $typeClassName . ';' . PHP_EOL;
			$file = $blockName . '-block-type.ts';
			echo 'Block type: ' . $file . ' [' . $typeClassName . ']<br/>' . PHP_EOL;
		}
	}
}