<?php

namespace WebImage\Blocks\Templates\Transpilers\Plugins;

use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Parsers\TemplateParser;
use WebImage\Blocks\Templates\Transpilers\TranspileException;
use WebImage\Blocks\Templates\Transpilers\TranspilerState;

class BlockMacroTranspiler extends AbstractTranspilerPlugin
{
    public function canPreProcess(TranspilerState $state, Branch $root): bool
    {
		return $this->anyBlockMacros($root);
    }

	/**
	 * Recrusively iterate through branch hierarchy and see if there are any @block macros
	 * @param Branch $branch
	 * @return bool
	 */
	private function anyBlockMacros(Branch $branch): bool
	{
		if ($branch->getType() == TemplateParser::T_MACRO && $branch->getValue() == 'block') return true;

		foreach($branch->getChildren() as $child) {
			if ($this->anyBlockMacros($child)) return true;
		}

		return false;
	}

    /**
     * @param TranspilerState $state
     * @param Branch $root
     * @return Branch
     * @throws TranspileException
     */
    public function preProcess(TranspilerState $state, Branch $root): Branch
    {
        return $this->moveAllBranchesUnderBlockMacro($root);
    }

    /**
     * As a convenience, the @block macro only appears at the top of the template without a closing tag.
     * This moves all children under the @blockMacro branch
     * @param Branch $root
     * @return Branch
     * @throws TranspileException
     */
    private function moveAllBranchesUnderBlockMacro(Branch $root): Branch
    {
        /** @var Branch[] $blockMacros */
        $blockMacros = [];
        $root        = $this->removeBlockMacros($root, $blockMacros);

        if (count($blockMacros) != 1) {
            throw new TranspileException(count($blockMacros) < 1 ? 'At least one @block macro must be specified' : 'Only one @block macro is supported');
        }

		$rootClass       = get_class($root);
		$blockMacroClass = get_class($blockMacros[0]);
		$children        = array_merge($root->getChildren(), $blockMacros[0]->getChildren());

        $root = new $rootClass(
            $root->getType(),
            $root->getValue(),
            [
                new $blockMacroClass(
                    $blockMacros[0]->getType(),
                    $blockMacros[0]->getValue(),
                    $children,
                    $blockMacros[0]->getArgs()
                )
            ],
            $root->getArgs()
        );

        return $root;
    }

    private function removeBlockMacros(Branch $branch, array &$blockMacros): Branch
    {
        $class = get_class($branch);

        return new $class(
            $branch->getType(),
            $branch->getValue(),
            array_values(array_filter($branch->getChildren(), function (Branch $child) use (&$blockMacros) {
                if ($child->getType() == TemplateParser::T_MACRO && $child->getValue() == 'block') {
                    $blockMacros[] = $child;
                    return false;
                }
                return true;
            })),
            $branch->getArgs()
        );
    }

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_MACRO && $branch->getValue() == 'block';
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
        return
            $this->getBlockCommentHeader($state, $branch) .
            $this->getBlockComment($state, $branch) .
            $this->getBlockCommentFooter($state, $branch) .
            $this->renderBlockHeader($state, $branch) .
            $this->renderBlockBody($state, $branch) .
            $this->renderBlockFooter($state, $branch);
    }

    protected function getBlockCommentHeader(TranspilerState $state, Branch $branch): string
    {
        return '';
    }

    protected function getBlockComment(TranspilerState $state, Branch $branch): string
    {
        $meta = $state->getMeta();
        return <<<EOT
/**
 * Block name: {$meta['block.name']}
 * Block class: {$meta['block.class']}
 * Block label: {$meta['block.label']}
 */

EOT;
    }

    protected function getBlockCommentFooter(TranspilerState $state, Branch $branch): string
    {
        return '';
    }

    protected function renderBlockHeader(TranspilerState $state, Branch $branch): string
    {
        return '';
    }

    protected function renderBlockBody(TranspilerState $state, Branch $branch): string
    {
        return $state->getTranspiler()->transpileBranches($branch->getChildren());
    }

    protected function renderBlockFooter(TranspilerState $state, Branch $branch): string
    {
        return '';
    }
}
