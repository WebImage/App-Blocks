<?php

namespace WebImage\BlockManager\Templates\Plugins;

use WebImage\BlockManager\Templates\IndentHelper;
use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\Plugins\AbstractTranspilerPlugin;
use WebImage\BlockManager\Templates\Transpilers\TranspileException;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;
use WebImage\BlockManager\Templates\Transpilers\VariableInterface;

class Variable extends AbstractTranspilerPlugin implements VariableInterface
{
    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_VARIABLE;
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
        $meta = $branch->getMeta();

        if (isset($meta[TemplateParser::META_SAFETY_CHECKS]) && $meta[TemplateParser::META_SAFETY_CHECKS] === false) {
            return $this->renderVariable($branch);
        }

        return $this->renderVariableSafely($state, $branch);
    }

    public function renderVariable(Branch $branch): string
    {
        return $this->formatVariable($branch->getValue());
    }

    public function formatVariable(string $varName): string
    {
        return $varName;
    }

    protected function renderVariableIsDefinedCheck(TranspilerState $state, Branch $branch): string
    {
        return $this->renderVariable($branch);
    }

    private function renderVariableOrDefault(TranspilerState $state, Branch $branch): string
    {
        $default = $this->renderDefaultVariableValue($state, $branch);
        $check = $this->renderVariableIsDefinedCheck($state, $branch);
        return sprintf('%s ? %s : %s', $check, $this->renderVariable($branch), $default);
    }

    private function renderVariableSafely(TranspilerState $state, Branch $branch): string
    {
        $vars = $this->extractVariables($branch);
        if ($branch->getType() != TemplateParser::T_VARIABLE) throw new TranspileException(__METHOD__ . ' can only be called on type ' . TemplateParser::T_VARIABLE);

        $initValues = array_map(function (Branch $var) use ($state) {
            return $this->renderVariableOrDefault($state, $var);
        }, $vars);

        $varName = $this->renderVariable($vars[0]);
        $tab     = str_repeat(' ', 4);

        $output = '';

        if (count($branch->getChildren()) == 0 && count($initValues) == 1) {
            $output .= $initValues[0];
        } else {
            $output .= '/* Safely render: ' . implode(', ', $this->friendlyVariableNamesForComments($branch)) . ' */' . PHP_EOL;
            $varNames = array_map(function(Branch $branch) {
                return $this->renderVariable($branch);
            }, $vars);

            $internalValueVar = $this->formatVariable('value');

            $output .= '(function(' . implode(', ', $varNames) . ') {' . PHP_EOL;
            $output .= $tab . $this->renderVariableInitialization($internalValueVar, $varName) . PHP_EOL;
            $output .= $tab . 'if (' . $internalValueVar . ' === null) return null;' . PHP_EOL . PHP_EOL;

            // Each child represents a greater depth into the variable that must be checked for
            foreach ($branch->getChildren() as $childBranch) {
                $output .= IndentHelper::indent(
                    $this->renderVariableChildBranch($internalValueVar, $state, $childBranch),
                    $tab,
                    1
                );
            }

            $output .= $tab . 'return ' . $internalValueVar . ';' . PHP_EOL;
            $output .= '})(' . implode(', ', $initValues) . ')';
        }

        return $output;
    }

    protected function renderVariableChildBranch(string $var, TranspilerState $state, Branch $childBranch): string
    {
        return '';
    }

    protected function renderFunctionArguments(array $arguments): ?string
    {
        $output = [];
        for ($i = 0, $j = count($arguments); $i < $j; $i++) {
            $output[] = $this->renderFunctionArgument($arguments, $i);
        }

        return implode(', ', $output);
    }

    protected function renderFunctionArgument(array $arguments, int $index): ?string
    {
        if ($index >= count($arguments)) return null;

        return $this->transpile($arguments[$index]);
    }

    protected function renderVariableInitialization(string $variable, string $value): string
    {
        return sprintf('%s = %s;', $variable, $variable);
    }

    /**
     * @param Branch $variableBranch
     * @return string[]
     */
    protected function friendlyVariableNamesForComments(Branch $variableBranch): array
    {
        if ($variableBranch->getType() != TemplateParser::T_VARIABLE) throw new TranspileException(__METHOD__ . ' can only be called on type ' . TemplateParser::T_VARIABLE);

        $depthSeparator = '.';
        $vars = [$variableBranch->getValue()];

        foreach ($variableBranch->getChildren() as $child) {
            switch ($child->getType()) {
                case TemplateParser::T_FUNCTION:
                    $var = $child->getValue() . '(';
                    foreach ($child->getArgs() as $arg) {
                        // @TODO Handle other types of arguments
                        switch ($arg->getType()) {
                            case TemplateParser::T_VARIABLE:
                                $vars = array_merge($vars, $this->extractVariableNames($arg));
                                break;
                        }
                    }
                    $var                    .= ')';
                    $vars[count($vars) - 1] .= $depthSeparator . $var;
                    break;
                case TemplateParser::T_PROPERTY_OR_FUNC:
                    $vars[count($vars) - 1] .= $depthSeparator . $child->getValue();
                    break;
            }
        }

        return $vars;
    }

    /**
     * Get all T_VARIABLES from the Branch
     * @param Branch $variableBranch
     * @return Branch[]
     * @throws TranspileException
     */
    protected function extractVariables(Branch $variableBranch): array
    {
        if ($variableBranch->getType() != TemplateParser::T_VARIABLE) throw new TranspileException(__METHOD__ . ' can only be called on type ' . TemplateParser::T_VARIABLE);

        $branches = [$variableBranch];

        foreach ($variableBranch->getChildren() as $child) {
            switch ($child->getType()) {
                case TemplateParser::T_FUNCTION:
                    foreach ($child->getArgs() as $arg) {
                        switch ($arg->getType()) {
                            case TemplateParser::T_VARIABLE:
                                $branches = array_merge($branches, $this->extractVariables($arg));
                                break;
                        }
                    }
                    break;
                case TemplateParser::T_PROPERTY_OR_FUNC:
                    break;
            }
        }

        return $branches;
    }

    protected function renderDefaultVariableValue(TranspilerState $state, Branch $branch)
    {
        $default = 'null';
        $meta = $branch->getMeta();

        if (isset($meta['default'])) {
            if (!($meta['default'] instanceof Branch)) throw new TranspileException('Variable default must be of type Branch');

            $default = $state->getTranspiler()->transpile($meta['default']);
        }

        return $default;
    }


    /**
     * Drill-down through variable branch to find other global variables that might be needed
     * @param Branch $variableBranch
     * @return string[]
     */
    protected function extractVariableNames(Branch $variableBranch): array
    {
        $vars = self::extractVariables($variableBranch);

        return array_map(function (Branch $branch) {
            return $branch->getValue();
        }, $vars);
    }
}
