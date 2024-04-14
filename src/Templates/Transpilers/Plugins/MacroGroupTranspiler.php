<?php

namespace WebImage\BlockManager\Templates\Transpilers\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Transpilers\TranspileException;
use WebImage\BlockManager\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class MacroGroupTranspiler extends AbstractTranspilerPlugin
{
    /** @var TranspilerPluginInterface[] */
    private array  $macros = [];
    private string $branchType;
    private bool $ignoreUnknownMacros;

    /**
     * @param array $macros
     */
    public function __construct(string $branchType, array $macros, bool $ignoreUnknownMacros=true)
    {
        $this->branchType = $branchType;
        foreach($macros as $macro) {
            $this->addMacro($macro);
        }
        $this->ignoreUnknownMacros = $ignoreUnknownMacros;
    }

    public function canPreProcess(TranspilerState $state, Branch $root): bool
    {
        foreach($this->macros as $macro) {
            if ($macro->canPreProcess($state, $root)) return true;
        }

        return false;
    }

    public function preProcess(TranspilerState $state, Branch $root): Branch
    {
        foreach($this->macros as $macro) {
            if ($macro->canPreProcess($state, $root)) {
                $root = $macro->preProcess($state, $root);
            }
        }
        return $root;
    }


    private function addMacro(TranspilerPluginInterface $renderer)
    {
        $this->macros[] = $renderer;
    }

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
//		if ($branch->getType() == $this->branchType) return true;

//		echo 'CHECKING SECONDARY: ' . $branch->getType() . '<br/>' . PHP_EOL;
//		echo '<div style="margin-left: 20px;">' . PHP_EOL;
//		foreach($this->macros as $macro) {
//			echo get_class($macro) . ' = ' . ($macro->canTranspile($state, $branch) ? 'true':'false') . '<br/>' . PHP_EOL;
//			if ($macro->canTranspile($state, $branch)) return true;
//		}
//		echo '</div>';
//
//		return false;
		return $branch->getType() == $this->branchType;
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
        foreach($this->macros as $macro) {
            if ($macro->canTranspile($state, $branch)) return $macro->transpile($state, $branch);
        }

        if ($this->ignoreUnknownMacros) return '';

        throw new TranspileException('No handler for @' . $branch->getValue() . ' macro');
    }
}
