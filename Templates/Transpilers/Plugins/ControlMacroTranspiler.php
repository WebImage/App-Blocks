<?php

namespace WebImage\BlockManager\Templates\Transpilers\Plugins;

use WebImage\BlockManager\Templates\Meta;
use WebImage\BlockManager\Templates\Parsers\Plugins\ControlMacroParser;
use WebImage\BlockManager\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class ControlMacroTranspiler extends AbstractTranspilerPlugin
{
    public function canPreProcess(TranspilerState $state, Branch $root): bool
    {
        return true;
    }

    public function preProcess(TranspilerState $state, Branch $root): Branch
    {
        $controls = $state->getMeta()[ControlMacroParser::META_CONTROLS] ?? new Meta();

        $root = $this->crawlBranch($root, $controls);

        return $root;
    }

    private function crawlBranch(Branch $branch, Meta $controls): Branch
    {
        $varName = $branch->getValue();
        if ($branch->getType() == TemplateParser::T_VARIABLE && isset($controls[$varName])) {
            $branch = $this->changeVariables($branch, $controls);
        } else {
            $branch = new Branch(
                $branch->getType(),
                $branch->getValue(),
                $this->changeBranches($branch->getChildren(), $controls),
                $this->changeBranches($branch->getArgs(), $controls),
                $branch->getMeta()->toArray()
            );
        }

        return $branch;
    }

    /**
     * Expand control-based variables to their full path - which will be a property on block.data
     * @param Branch $branch
     * @param Meta $controls
     * @return Branch
     */
    private function changeVariables(Branch $branch, Meta $controls): ?Branch
    {
        return $branch;
//
//        if ($branch->getType() != TemplateParser::T_VARIABLE || !isset($controls[$branch->getValue()])) return $branch;
//
//        $branch = new Branch($branch->getType(), 'block', [
//            new Branch(TemplateParser::T_PROPERTY_OR_FUNC, 'data'),
//            new Branch(TemplateParser::T_PROPERTY_OR_FUNC, $branch->getValue())
//        ]);
//
//        return $branch;
    }

    private function changeBranches(array $branches, Meta $controls): array
    {
        return array_map(function(Branch $branch) use ($controls) {
            return $this->crawlBranch($branch, $controls);
        }, $branches);
    }



    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_MACRO && in_array($branch->getValue(), ['control', 'endcontrol']);
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
        return '';
    }
}
