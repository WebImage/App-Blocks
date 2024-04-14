<?php

namespace WebImage\BlockManager\Processing;

use WebImage\BlockManager\Templates\Parsers\ParseResult;
use WebImage\BlockManager\Templates\Parsers\Plugins\BlockMacroParser;
use WebImage\BlockManager\Templates\Transpilers\ReactTemplateTranspiler;

class ReactControlTemplateGenerator implements ProcessorInterface
{
	private string                  $controlsPath;
	private ReactTemplateTranspiler $transpiler;

	/**
	 * @param string $controlsPath
	 */
	public function __construct(string $controlsPath, ?ReactTemplateTranspiler $transpiler = null)
	{
		$controlsPath = rtrim($controlsPath, '/\\');
		$this->assertDirectoryExists($controlsPath);
		$this->controlsPath = $controlsPath;
		$this->transpiler   = $transpiler ?: new ReactTemplateTranspiler();
	}

	/**
	 * @inheritDoc
	 */
	public function process(array $blockFiles): void
	{
//		echo 'Write to ' . $this->controlsPath . '<br/>' . PHP_EOL;
		foreach ($blockFiles as $blockFile) {
			$meta = $blockFile->getResult()->getMeta();
			if (!isset($meta[BlockMacroParser::META_BLOCK_NAME])) continue;
			echo 'React Block: ' . $meta[BlockMacroParser::META_BLOCK_NAME] . '<br/>' . PHP_EOL;
//			/** @var ParseResult $parsed */
//			$parsed = $blockFile->getResult();
//			$jsxFile = $this->controlsPath . '/' . basename($blockFile->getFile()) . '.tsx';
//
//			file_put_contents($jsxFile, $this->transpiler->transpile($parsed->getRoot(), $parsed->getMeta()));
		}
//		die(__FILE__ . ':' . __LINE__ . '<br />' . PHP_EOL);
	}

	private function assertDirectoryExists(string $path): void
	{
		// Create directory if it does not exist
		if (!is_dir($path)) mkdir($path);

		// If the directory still not does not exist then fail
		if (!is_dir($path)) {
			throw new \InvalidArgumentException('Directory does not exist: ' . $path);
		}
	}
}