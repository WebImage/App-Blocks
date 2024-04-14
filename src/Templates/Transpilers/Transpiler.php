<?php

namespace WebImage\BlockManager\src\Templates\Transpilers;

use WebImage\BlockManager\src\Templates\Context;
use WebImage\BlockManager\src\Templates\IndentHelper;
use WebImage\BlockManager\src\Templates\Lexers\TemplateLexer;
use WebImage\BlockManager\src\Templates\Meta;
use WebImage\BlockManager\src\Templates\Parsers\Branch;
use WebImage\BlockManager\src\Templates\Parsers\ParseResult;
use WebImage\BlockManager\src\Templates\Parsers\TemplateParser;

class Transpiler implements TranspilerInterface
{
    private string $indent = '    '; // The string to use for indenting
    protected array $supportedFeatures = [];
    /**
     * @var TranspilerPluginInterface[]
     */
    private array            $plugins = [];
    private ?TranspilerState $state   = null;

    protected function renderOutputHeader(): string
    {
        return '';
    }

    protected function renderOutputFooter(): string
    {
        return '';
    }

    private function preProcess(Branch $root): Branch
    {
        foreach ($this->plugins as $plugin) {
            if ($plugin->canPreProcess($this->state, $root)) {
                $root = $plugin->preProcess($this->state, $root);
            };
        }

        return $root;
    }

    public function transpile(Branch $branch, Meta $meta = null, Context $context = null): string
    {
        $output = '';

        $restoreState = $this->state;
        $isRoot       = $this->initializeState($meta, $context);

        if ($isRoot) {
            $branch = $this->preProcess($branch);
        }

        foreach ($this->plugins as $plugin) {
            if (!($plugin instanceof TranspilerPluginInterface) || !$plugin->canTranspile($this->state, $branch)) continue;
            return $plugin->transpile($this->state, $branch);
        }

        switch ($branch->getType()) {
            case TemplateParser::T_ROOT:
            case TemplateParser::T_CHILDREN:
                /** @TODO It's tempting to put a check for (!$isRoot) here, but let's not in case another transpiler wants to insert records above a root? */
                $output .= $this->transpileBranches($branch->getChildren());
                break;
            case TemplateParser::T_LITERAL:
                $output .= $branch->getValue();
                break;
            case TemplateParser::T_STRING:
                $output .= $this->transpileString($branch);
                break;
//            case TemplateParser::T_VARIABLE:
//                $output .= $this->transpileVariable($branch);
//                break;
//            case TemplateParser::T_CODE:
//                $output .= $this->transpileCode($branch);
//                break;
            case TemplateParser::T_INLINE_COMMENT: // @TODO Add comments to code?
            case TemplateParser::T_BLOCK_COMMENT:
                break;
            default:
                throw new TranspileException('Unknown branch ' . $branch->getType() . '.  The transpiler may be missing a plugin.');
        }

        $this->state = $restoreState;

        if ($isRoot) $output = $this->renderOutputHeader() . $output . $this->renderOutputFooter();

        return $output;
    }

	/**
	 * @throws TranspileException
	 */
	public function transpileBranches(array $branches): string
    {
        $output = '';

        foreach ($branches as $branch) {
            if (!($branch instanceof Branch)) throw new TranspileException('Expected children to be of type ' . Branch::class . ', but ' . gettype($branch) . ' found');
            $output .= $this->transpile($branch);
        }

        return $output;
    }

    /**
     * Initialize state for transpiling
     * @param Meta|null $meta
     * @param Context|null $context
     * @return bool TRUE if this is a new initialization, or FALSE if the state was previously established
     */
    private function initializeState(Meta $meta = null, Context $context = null): bool
    {
        if ($this->state === null) {
            if ($meta === null) $meta = new Meta();
            else $meta = new Meta($meta->toArray());
            if ($context === null) $context = new Context();

            $meta[self::META_SUPPORT_FEATURES] = $this->supportedFeatures;
            $this->state                       = new TranspilerState($this, $meta, $context);
            $this->assertMinimumTranspilers($this->state);
            return true;
        } else {
            // @TODO Do we want $meta or $context to be overridable in subsequent calls?  Probably not
            if ($meta !== null) TranspileException::forEmbeddedStateInstantiation(__CLASS__ . '::transpile', 'meta');
            if ($context !== null) TranspileException::forEmbeddedStateInstantiation(__CLASS__ . '::transpile', 'context');
            return false;
        }
    }

    private function assertMinimumTranspilers(TranspilerState $state): void
    {
        // Initialize all required transpilers
        $minBranchTypes = [
            TemplateParser::T_VARIABLE,
            TemplateParser::T_CODE
        ];

        foreach($minBranchTypes as $branchType) {
            $branch = new Branch($branchType);
            /** @var TranspilerPluginInterface $plugin */
            foreach($this->plugins as $plugin) {
                if ($plugin->canTranspile($state, $branch)) {
                    continue 2;
                }
            }
            throw new TranspileException('Missing plugin to handle ' . $branchType);
        }
    }

    protected function transpileString(Branch $branch): string
    {
        $value = $branch->getValue();
        if (isset($branch->getMeta()[TemplateParser::META_QUOTE_TYPE])) {
            $quoteType = $branch->getMeta()[TemplateParser::META_QUOTE_TYPE];
            if ($quoteType == TemplateLexer::T_DOUBLE_QUOTE) $value = sprintf('"%s"', $value);
            else if ($quoteType == TemplateLexer::T_SINGLE_QUOTE) $value = sprintf("'%s'", $value);
        }
        return $value;
    }

    protected function getRenderCode(Branch $branch): array
    {
        return array_map(function (Branch $childBranch) {
            $value = $this->transpile($childBranch);
            if ($childBranch->getType() == TemplateParser::T_STRING) $value = sprintf("'%s'", $value);
            return $value;
        }, $branch->getChildren());
    }

    public function plugin(TranspilerPluginInterface $plugin): void
    {
        $this->plugins[] = $plugin;
    }

    public function plugins(): array
    {
        return $this->plugins;
    }

    public function indent(string $text, int $depth=1): string {
        return IndentHelper::indent($text, $this->indent, $depth);
    }

    public function __invoke(ParseResult $result)
    {
        return $this->transpile($result->getRoot(), $result->getMeta());
    }

//    protected function setIndent(string $indent): void
//    {
//        $this->indent = $indent;
//    }

//    abstract protected function transpileVariable(Branch $branch): string;
//
//    abstract protected function transpileCode(Branch $branch): string;
}
