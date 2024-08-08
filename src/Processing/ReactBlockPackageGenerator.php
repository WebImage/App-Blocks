<?php

namespace WebImage\BlockManager\Processing;

use WebImage\BlockManager\Templates\Parsers\Plugins\BlockMacroParser;
use WebImage\BlockManager\Templates\Plugins\PropertyMacro;
use WebImage\BlockManager\Templates\Transpilers\ReactTemplateTranspiler;

class ReactBlockPackageGenerator implements ProcessorInterface
{
	public function process(array $blockFiles): void
	{
		foreach ($blockFiles as $blockFile) {
			$meta           = $blockFile->getResult()->getMeta();
			$className      = $meta[BlockMacroParser::META_BLOCK_CLASS];
			$typeClassName  = sprintf('%sBlockType', $className);
			$definitionName = sprintf('%sBlockDefinition', $className);

			$properties = $meta[PropertyMacro::META_PROPERTIES] ?? [];

			$output = 'import BlockTypeDefinition from \'../types/block-type-definition\'' . PHP_EOL;
			$output .= 'import ' . $typeClassName . ' from \'../types/block-types/text-block-type\'' . PHP_EOL;
			$output .= 'import TextConfigPanel from \'../components/blocks/config-panels/TextConfigPanel\'' . PHP_EOL;
			$output .= 'import Text from \'../components/blocks/Text\'' . PHP_EOL;
			$output .= PHP_EOL;
			$output .= 'const ' . $definitionName . ': BlockTypeDefinition<' . $typeClassName . '> = {' . PHP_EOL;
			$output .= '  type: \'' . $meta[BlockMacroParser::META_BLOCK_NAME] . '\',' . PHP_EOL;
			$output .= '  defaultConfig: {},' . PHP_EOL;
			$output .= '  defaultData: {' . PHP_EOL;

			$transpiler = new ReactTemplateTranspiler();
			foreach($properties as $property => $propDef) {
				if (!array_key_exists('default', $propDef)) continue;
				$output .= '    ' . $property . ': ' . $transpiler->transpile($propDef['default']) . ', ' . PHP_EOL;
			}
			$output .= '},' . PHP_EOL;
			$output .= 'configPanel: TextConfigPanel,' . PHP_EOL;
			$output .= 'designer: Text,' . PHP_EOL;
			$output .= '};' . PHP_EOL;
			$output .= PHP_EOL;
			$output .= 'export default ' . $definitionName . ';' . PHP_EOL;

			$file = $meta[BlockMacroParser::META_BLOCK_NAME];
			echo 'React Block Package: ' . $file . '<br/>' . PHP_EOL;
		}
	}
}