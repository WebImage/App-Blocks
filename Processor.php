<?php

namespace WebImage\BlockManager;

use WebImage\BlockManager\Templates\Parsers\TemplateParser;



class Processor
{
    /**
     * @param TemplateParser $parser
     */
    public function __construct()
    {
    }

    public function source(array $files): BlockState
    {
        $files = array_map(function($file) {
            return new BlockFile($file, $file);
        }, $files);

        return new BlockState($files);
    }
}
