<?php

namespace WebImage\BlockManager\Templates\Transpilers\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\Plugins\HtmlParser;
use WebImage\BlockManager\Templates\Transpilers\TranspileException;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class HtmlTranspiler extends AbstractTranspilerPlugin
{
    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return in_array($branch->getType(), [
            HtmlParser::T_HTML_OPENCLOSE_TAG,
            HtmlParser::T_HTML_TAG,
            HtmlParser::T_HTML_DYNAMIC_TAG,
            HtmlParser::T_HTML_ATTRIBUTE
        ]);
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
        switch($branch->getType()) {
            case HtmlParser::T_HTML_OPENCLOSE_TAG:
                return sprintf('<%s%s/>', $this->renderTagName($state, $branch), $this->renderAttributeString($state, $branch));
            case HtmlParser::T_HTML_TAG:
            case HtmlParser::T_HTML_DYNAMIC_TAG:
                return sprintf(
                    '<%s%s>%s</%1$s>',
                    $this->renderTagName($state, $branch), // $branch->getValue()
                    $this->renderAttributeString($state, $branch),
                    $state->getTranspiler()->transpileBranches($branch->getChildren())
                );
            case HtmlParser::T_HTML_TAG_NAME:
                return $state->getTranspiler()->transpileBranches($branch->getChildren());
            case HtmlParser::T_HTML_ATTRIBUTE:
                return sprintf('%s', $this->renderAttribute($state, $branch));
            default:
                throw new TranspileException('Unknown type: ' . $branch->getType() . ' in ' . __CLASS__);
        }
    }

    protected function renderTagName(TranspilerState $state, Branch $branch): string
    {
        if ($branch->getType() == HtmlParser::T_HTML_DYNAMIC_TAG) {
            $nameArgs = array_filter($branch->getArgs(), function(Branch $branch) {
                return $branch->getType() == HtmlParser::T_HTML_TAG_NAME;
            });

            $output = '';
            foreach($nameArgs as $nameArg) {
                $output .= $this->transpile($state, $nameArg);
            }

            return $output;
        }

        return $branch->getValue();
    }

    protected function renderAttributeString(TranspilerState $state, Branch $branch): string
    {
        $args = array_filter($branch->getArgs(), function(Branch $branch) {
            return $branch->getType() == HtmlParser::T_HTML_ATTRIBUTE;
        });

        $attrs = array_map(function (Branch $branch) use ($state) {
            return $state->getTranspiler()->transpile($branch/*, $state->getMeta(), $state->getContext()*/);
        }, $args);

        return /*(count($attrs) > 0 ? ' ' : '') . */implode('', $attrs);
    }

    protected function renderAttribute(TranspilerState $state, Branch $branch): string
    {
        $attrName  = $this->renderAttributeName($state, $branch);
        $attrValue = $this->renderAttributeValue($state, $branch, $attrName);

        return sprintf(' %s=%s', $attrName, $attrValue);
    }

    protected function renderAttributeName(TranspilerState $state, Branch $branch): string
    {
        return $branch->getValue();
    }

    protected function renderAttributeValue(TranspilerState $state, Branch $branch, string $attrName): string
    {
        return $state->getTranspiler()->transpileBranches($branch->getArgs());

//        return $this->wrapAttributeValue($branch, $value);
    }
//    /**
//     * Wrap attribute in quotes if it is a pure string
//     * @param Branch $attrValBranch
//     * @param string $attrVal
//     * @return string
//     */
//    protected function wrapAttributeValue(Branch $attrValBranch, string $attrVal): string
//    {
//
//        $attrs = $attrValBranch->getArgs();
//        foreach($attrs as $attr) {
//            if ($attr->getType() != TemplateParser::T_STRING) return $attrVal;
//        }
//		echo '<pre>';print_r($attrValBranch); die(__FILE__ . ':' . __LINE__ . PHP_EOL);
//        return sprintf('"%s"', $attrVal);
//    }
}
