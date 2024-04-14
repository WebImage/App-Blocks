<?php

namespace WebImage\BlockManager\Templates\Transpilers\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\Plugins\IfSupportsMacroParser;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\TranspilerInterface;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class IfSupportsMacroGroupTranspiler extends AbstractTranspilerPlugin
{
    public function canPreProcess(TranspilerState $state, Branch $root): bool
    {
        return true;
    }

    public function preProcess(TranspilerState $state, Branch $root): Branch
    {
        $branch = $this->processBranch($state, $root);
//        $branch = $this->removeUnsupportedBranches($state, $root);
//
        if ($branch === null) {
            $branch = new Branch(TemplateParser::T_ROOT);
        }

        return $branch;
    }

    private function processBranch(TranspilerState $state, Branch $branch): ?Branch
    {
        $children = $this->processBranches($state, $branch->getChildren());
        $args = $this->processBranches($state, $branch->getArgs());

        return new Branch($branch->getType(), $branch->getValue(), $children, $args, $branch->getMeta()->toArray());
    }

    /**
     * @param Branch[] $branches
     * @return Branch[]
     */
    public function processBranches(TranspilerState $state, array $branches): array
    {
        $revised = [];

        foreach($branches as $branch) {
            if ($branch->getType() == IfSupportsMacroParser::T_IF_SUPPORTS_BRANCH_STACK) {
                $revised = [...$revised, ...$this->evaluateIf($state, $branch)];
            } else {
                $revised[] = $this->processBranch($state, $branch);
            }
        }

        return $revised;
    }

    /**
     * Evaluate if conditions and return children of the matching condition (if found)
     * @param Branch $ifBranch
     * @return array
     */
    public function evaluateIf(TranspilerState $state, Branch $ifBranch): array
    {
        $branches = [];

        $supportedFeatures = $state->getMeta()[TranspilerInterface::META_SUPPORT_FEATURES] ?? [];

        foreach($ifBranch->getChildren() as $branchChild) {
            $anyTrue = count($branchChild->getArgs()) == 0;
            foreach($branchChild->getArgs() as $arg) {
                if (in_array($arg->getValue(), $supportedFeatures)) {
                    $anyTrue = true;
                    break;
                }
            }

            if ($anyTrue) {
//                $branches = $this->processBranches($state, $branchChild->getChildren());
                $branches = $branchChild->getChildren();
                break;
            }
        }

        return $branches;
    }
//    private function removeUnsupportedBranches(TranspilerState $state, Branch $branch): ?Branch
//    {
//        $supportedBranch = $this->evaluateBranch($state, $branch);
//
//        if ($supportedBranch === null) return null;
//
//        $children = array_filter(array_map(function(Branch $branch) use ($state) {
//            return self::removeUnsupportedBranches($state, $branch);
//        }, $supportedBranch->getChildren()));
//
//        $args = array_filter(array_map(function(Branch $branch) use ($state) {
//            return self::removeUnsupportedBranches($state, $branch);
//        }, $supportedBranch->getArgs()));
//
//        return new Branch($supportedBranch->getType(), $supportedBranch->getValue(), $children, $args, $supportedBranch->getMeta()->toArray());
//    }
//
//    private function evaluateBranch(TranspilerState $state, Branch $branch): ?Branch
//    {
//        if ($branch->getType() != IfSupportsMacroParser::T_IF_SUPPORTS_BRANCH_STACK) return $branch;
//
//        $children = [];
//
//        $supportedFeatures = $state->getMeta()[TranspilerInterface::META_SUPPORT_FEATURES] ?? [];
//
//        foreach($branch->getChildren() as $branchChild) {
//            $anyTrue = count($branchChild->getArgs()) == 0;
//            foreach($branchChild->getArgs() as $arg) {
//                if (in_array($arg->getValue(), $supportedFeatures)) {
//                    $anyTrue = true;
//                    $children = $branchChild->getChildren();
//                    break;
//                }
//            }
//
//            if ($anyTrue) {
//                $children = $branchChild->getChildren();
//                break;
//            }
//        }
//
//        return new Branch(TemplateParser::T_CHILDREN, null, $children);
//    }
}
