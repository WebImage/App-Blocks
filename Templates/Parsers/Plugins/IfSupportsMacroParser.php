<?php

namespace WebImage\BlockManager\Templates\Parsers\Plugins;

use WebImage\BlockManager\Templates\Context;
use WebImage\BlockManager\Templates\Lexers\TemplateLexerDebugger;
use WebImage\BlockManager\Templates\Meta;
use WebImage\BlockManager\Templates\Parsers\ParserException;
use WebImage\BlockManager\Templates\Parsers\ParserState;
use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\BranchArgumentDefinition;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;

class IfSupportsMacroParser extends AbstractMacroParser
{
    const CONTEXT_LOGIC_STACK = 'ifSupports.logicStack';
    const T_IF_SUPPORTS_BRANCH_STACK = 'T_IF_SUPPORTS_BRANCH_STACK';
    const T_IF_SUPPORTS_BRANCH = 'T_IF_SUPPORTS_BRANCH';
    const T_IF_SUPPORTS = 'T_IF_SUPPORTS';
    const T_ELSE_IF_SUPPORTS = 'T_ELSE_IF_SUPPORTS';
    const T_ELSE_SUPPORTS = 'T_ELSE_SUPPORTS';
    const T_END_IF_SUPPORTS = 'T_END_IF_SUPPORTS';

    protected array $supportedMacros = ['ifSupports', 'endIfSupports', 'elseIfSupports', 'elseSupports'];

    protected function startContext(): ?Meta
    {
        if ($this->getMacroName() == 'ifSupports') {
            return $this->currentState->context->push([
                                                          self::CONTEXT_LOGIC_STACK => []
                                                      ]);
        }

//        if ($this->getMacroName() == 'endIfSupports') {
//
//        }
        return null;
    }

    protected function createBranch(ParserState $state, string $type, string $name, array $children = [], array $args = [], array $meta = []): ?Branch
    {
        switch($type) {
            case self::T_IF_SUPPORTS:
                /**
                 * 1. Remove logic from children
                 * 2. Create top-level IF branch
                 * 3. Get logic stored from other branches (e.g. elseIf, else)
                 * 4. Add IF branch to top of logic branches
                 */
                $ifChildren = $this->extractLogicBranches($state->context, $children); // 1
                $ifBranch   = parent::createBranch($state, $type, $name, $ifChildren, $args); // 2
                $children   = $state->context[self::CONTEXT_LOGIC_STACK]; // 3
                array_unshift($children, $ifBranch); // 4

                // Redefine all children as BRANCHes
                $branches = array_map(function(Branch $branch) {
                    return new Branch(self::T_IF_SUPPORTS_BRANCH, $branch->getValue(), $branch->getChildren(), $branch->getArgs(), $branch->getMeta()->toArray());
                }, $children);

                return new Branch(self::T_IF_SUPPORTS_BRANCH_STACK, null, $branches);
            case self::T_ELSE_SUPPORTS:
            case self::T_ELSE_IF_SUPPORTS:
                $children = $this->extractLogicBranches($state->context, $children);
                break;
            case self::T_END_IF_SUPPORTS:
                break;
        }

        return parent::createBranch($state, $type, $name, $children, $args, $meta);
    }

    public static function createIf(array $branches): Branch
    {
        return new Branch(self::T_IF_SUPPORTS_BRANCH_STACK, null, $branches);
    }

    /**
     * Create a condition that can be added to createIf(...) result.  Children will only be rendered if $args are all meta
     */
    public static function createIfCondition(array $ifArgs, array $rendereChildren): Branch
    {
        $ifArgs = array_map(function($arg) {
            if (is_string($arg)) return new Branch(TemplateParser::T_STRING, $arg);
            return $arg;
        }, $ifArgs);

        return new Branch(self::T_IF_SUPPORTS_BRANCH, null, $rendereChildren, $ifArgs);
    }

    /**
     * @inheritDoc
     */
    public static function getArgumentDefinitions(string $macroName): array
    {
        if (in_array($macroName, ['ifSupports', 'elseIfSupports'])) {
            return [
                new BranchArgumentDefinition('featureName', 'The name of the feature that is being checked for its existence.', true, true)
            ];
        }

        return [];
    }

    protected function getBranchType(string $macroName): string
    {
        switch ($macroName) {
            case 'ifSupports':
                return self::T_IF_SUPPORTS;
            case 'elseIfSupports':
                return self::T_ELSE_IF_SUPPORTS;
            case 'elseSupports':
                return self::T_ELSE_SUPPORTS;
            case 'endIfSupports':
                return self::T_END_IF_SUPPORTS;
            default:
                throw new ParserException('Unknown macro type: ' . $macroName);
        }
    }

    protected function processBody(ParserState $state, string $macroName): array
    {
        switch ($macroName) {
            case 'ifSupports':
            case 'elseIfSupports':
                return $state->parser->parseText([
                                                        self::T_ELSE_IF_SUPPORTS,
                                                        self::T_ELSE_SUPPORTS,
                                                        self::T_END_IF_SUPPORTS
                                                    ]);

            case 'elseSupports':
                return $state->parser->parseText([
                                                        self::T_END_IF_SUPPORTS
                                                    ]);

            case 'endIfSupports':
                break;

            default:
                echo 'Other: ' . $macroName . '<br/>' . PHP_EOL;
                die(__FILE__ . ':' . __LINE__ . '<br />' . PHP_EOL);
        }

        return [];
    }


    /**
     * @param array $branches
     * @return array | [Branch[], Branch[]]
     */
    private function extractLogicBranches(Context $context, array $branches)
    {
        $branches = $this->removeUnusedBranches($branches);
        $logicBranches = [self::T_IF_SUPPORTS, self::T_ELSE_IF_SUPPORTS, self::T_ELSE_SUPPORTS];

        if (count($branches) == 0) return $branches;

        if (!isset($context[self::CONTEXT_LOGIC_STACK])) {
            throw new ParserException(self::CONTEXT_LOGIC_STACK . ' has not been set up properly');
        }

        $count = 0;

        while (count($branches) > 0 && in_array($branches[count($branches) - 1]->getType(), $logicBranches)) {
            if (++$count > 100) die(__FILE__ . ':' . __LINE__ . '<br />' . PHP_EOL);

            $logicBranch = array_pop($branches);

            $context[self::CONTEXT_LOGIC_STACK] = array_merge([$logicBranch], $context[self::CONTEXT_LOGIC_STACK]);
        }

        return $branches;
    }

    /**
     * @param Branch[] $branches
     * @return void
     */
    private function removeUnusedBranches(array $branches)
    {
        if (count($branches) > 0 && $branches[count($branches) - 1]->getType() == self::T_END_IF_SUPPORTS) {
            array_pop($branches);
        }

        return $branches;
    }
}
