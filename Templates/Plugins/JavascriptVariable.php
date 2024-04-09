<?php

namespace WebImage\BlockManager\Templates\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Transpilers\TranspileException;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class JavascriptVariable extends Variable
{
    protected function renderVariableIsDefinedCheck(TranspilerState $state, Branch $branch): string
    {
        return sprintf('typeof(%s) !== \'undefined\'', $this->renderVariable($branch));
    }

    protected function renderVariableInitialization(string $variable, string $value): string
    {
        return sprintf('let %s = %s;', $variable, $value);
    }

    protected function renderVariableChildBranch(string $var, TranspilerState $state, Branch $childBranch): string
    {
        $output = '';

        switch ($childBranch->getType()) {
            case TemplateParser::T_PROPERTY_OR_FUNC:
                $name   = $childBranch->getValue();
                $output .= '// Checking T_PROPERTY_OR_FUNC for possible values' . PHP_EOL;
                $output .= 'if (typeof(' . $var . ') === \'object\' && typeof(' . $var . '.' . $name . ') === \'function\') ' . $var . ' = ' . $var . '.' . $name . '();' . PHP_EOL;
                $output .= 'else if (typeof(' . $var . ') === \'object\' && typeof(' . $var . '.' . $name . ') !== \'undefined\') ' . $var . ' = value.' . $name . ';' . PHP_EOL;
                $output .= 'else return undefined;' . PHP_EOL;
                break;

            case TemplateParser::T_FUNCTION:
                $output   .= '// Checking T_FUNC for methods' . PHP_EOL;
                $funcName = $childBranch->getValue();
                $output   .= 'if (typeof(' . $var . ') === \'object\' && typeof(' . $var . '.' . $funcName . ') === \'function\') ' . $var . ' = ' . $var . '.' . $funcName . '(' . $this->renderFunctionArguments($childBranch->getArgs()) . ');' . PHP_EOL;
                $output   .= 'else return undefined;' . PHP_EOL;
                break;
            default:
                throw new TranspileException('Unknown type: ' . $childBranch->getType());
        }

        return $output;
    }
}
