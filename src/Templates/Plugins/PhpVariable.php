<?php

namespace WebImage\BlockManager\src\Templates\Plugins;

use WebImage\BlockManager\src\Templates\Parsers\Branch;
use WebImage\BlockManager\src\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\src\Templates\Transpilers\TranspileException;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerState;

class PhpVariable extends Variable
{
    protected function renderVariableIsDefinedCheck(TranspilerState $state, Branch $branch): string
    {
        return sprintf('isset(%s)', $this->renderVariable($branch));
    }

    public function formatVariable(string $varName): string
    {
        return '$' . $varName;
    }

    protected function renderVariableInitialization(string $variable, string $value): string
    {
        return sprintf('%s = %s;', $variable, $value);
    }

    protected function renderVariableChildBranch(string $var, TranspilerState $state, Branch $childBranch): string
    {
        $output = '';

        switch ($childBranch->getType()) {
            case TemplateParser::T_PROPERTY_OR_FUNC:
                $name   = $childBranch->getValue();
                $output .= '// Checking T_PROPERTY_OR_FUNC for possible values' . PHP_EOL;
                $output .= 'if (is_array(' . $var . ') && array_key_exists(\'' . $name . '\', ' . $var . ')) ' . $var . ' = ' . $var . '[\'' . $name . '\'];' . PHP_EOL;
                $output .= 'else if (is_object(' . $var . ') && method_exists(' . $var . ', \'' . $name . '\')) ' . $var . ' = ' . $var . '->' . $name . '();' . PHP_EOL;
                $output .= 'else if (is_object(' . $var . ') && in_array(\'' . $name . '\', array_keys(get_object_vars(' . $var . ')))) ' . $var . ' = ' . $var . '->' . $name . ';' . PHP_EOL;
                $output .= 'else if (function_exists(\'' . $name . '\')) ' . $var . ' = ' . $name . '(' . $var . ');' . PHP_EOL;
                $output .= 'else return null;' . PHP_EOL;

                break;
            case TemplateParser::T_FUNCTION:
                $output   .= '// Checking T_FUNC for methods' . PHP_EOL;
                $funcName = $childBranch->getValue();
                $output   .= 'if (is_object(' . $var . ') && method_exists(' . $var . ', \'' . $funcName . '\')) ' . $var . ' = ' . $var . '->' . $funcName . '(' . $this->renderFunctionArguments($childBranch->getArgs()) . ');' . PHP_EOL;
                $output   .= 'else return null;' . PHP_EOL;
                break;
            default:
                throw new TranspileException('Unknown type: ' . $childBranch->getType());
        }

        return $output;
    }
}
