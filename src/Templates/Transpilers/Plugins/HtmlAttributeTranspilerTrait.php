<?php

namespace WebImage\Blocks\Templates\Transpilers\Plugins;

use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Transpilers\TranspilerState;

trait HtmlAttributeTranspilerTrait
{
    protected function renderAttributeName(TranspilerState $state, Branch $branch): string
    {
        return self::normalizeAttributeName(parent::renderAttributeName($state, $branch));
    }

    private static function normalizeAttributeName(string $name): string
    {
        return preg_replace_callback('/[A-Z]/', function($matches){
            return '-' . strtolower($matches[0]);
        }, $name);
    }

    protected function wrapAttributeValue(Branch $attrBrach, string $attrVal): string
    {
        return sprintf('"%s"', $attrVal);
    }
}
