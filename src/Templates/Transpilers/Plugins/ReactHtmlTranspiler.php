<?php

namespace WebImage\Blocks\Templates\Transpilers\Plugins;

use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Transpilers\TranspilerState;

class 	ReactHtmlTranspiler extends HtmlTranspiler
{
    protected function renderAttributeName(TranspilerState $state, Branch $branch): string
    {
        $attributeName = parent::renderAttributeName($state, $branch);

        /**
         * Rewrite "class" attribute to "className" for React
         */
        if ($attributeName == 'class') return 'className';

        return $attributeName;
    }

//    protected function wrapAttributeValue(Branch $attrValBranch, string $attrVal): string
//    {
//        $nonString = array_filter($attrValBranch->getArgs(), function(Branch $arg) {
//            return $arg->getType() != TemplateParser::T_STRING;
//        });
//
//        if ($attrValBranch->getType() == TemplateParser::T_CODE) return $attrVal;
//
//        return sprintf('"%s"', $attrVal);
//    }
}
