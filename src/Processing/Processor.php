<?php

namespace WebImage\Blocks\Processing;

use WebImage\Blocks\Templates\Parsers\TemplateParser;

class Processor
{
	private TemplateParser $parser;

	/**
	 * @param TemplateParser $parser
	 */
	public function __construct(TemplateParser $parser)
	{
		$this->parser = $parser;
	}

	public function source(array $files): BlockState
    {
        $files = array_map(function($file) {
			if (!file_exists($file)) throw new SourceException('Invalid source file: ' . $file);
            return new BlockFile($file, $this->parser->parse(file_get_contents($file)));
        }, $files);

        return new BlockState($files);
    }
}
