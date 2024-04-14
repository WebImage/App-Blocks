<?php

namespace WebImage\BlockManager\src\Templates\Transpilers;

use WebImage\BlockManager\src\Templates\Parsers\Plugins\WrapMacroParser;
use WebImage\BlockManager\src\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\src\Templates\Plugins\AuthorMacro;
use WebImage\BlockManager\src\Templates\Plugins\ControlOptionMacro;
use WebImage\BlockManager\src\Templates\Plugins\DraggableMacro;
use WebImage\BlockManager\src\Templates\Plugins\ExtendBlockMacro;
use WebImage\BlockManager\src\Templates\Plugins\PhpCode;
use WebImage\BlockManager\src\Templates\Plugins\PhpVariable;
use WebImage\BlockManager\src\Templates\Plugins\PropertyMacro;
use WebImage\BlockManager\src\Templates\Transpilers\Plugins\ControlMacroTranspiler;
use WebImage\BlockManager\src\Templates\Transpilers\Plugins\IfSupportsMacroGroupTranspiler;
use WebImage\BlockManager\src\Templates\Transpilers\Plugins\MacroGroupTranspiler;
use WebImage\BlockManager\src\Templates\Transpilers\Plugins\PhpBlockMacroTranspiler;
use WebImage\BlockManager\src\Templates\Transpilers\Plugins\PhpEachMacroTranspiler;
use WebImage\BlockManager\src\Templates\Transpilers\Plugins\PhpHtmlTranspiler;

class PhpTemplateTranspiler extends Transpiler
{
    protected array $supportedFeatures = ['php'];

//    protected function transpileCode(Branch $branch): string
//    {
/*        return '<?php echo ' . implode(' . ', $this->getRenderCode($branch)) . ' ?>';*/
//    }

//    protected function transpileVariable(Branch $branch): string
//    {
//        if (isset($branch->getMeta()[TemplateParser::META_SAFETY_CHECKS]) && $branch->getMeta()[TemplateParser::META_SAFETY_CHECKS] === false) {
//            return sprintf('$%s', $branch->getValue());
//        }
//
//        $vars = $this->extractVariables($branch);
//
//        $initValues = array_map(function (Branch $var) {
//            $default = 'null';
//            if (isset($var->getMeta()['default'])) {
//                $default = $this->transpile($var->getMeta()['default']);
//                if ($var->getMeta()['default']->getType() == TemplateParser::T_STRING) $default = sprintf("'%s'", $default);
//            }
//            return sprintf('isset($%s) ? $%1$s : %s', $var->getValue(), $default);
//        }, $vars);
//
//        $varName    = $vars[0]->getValue();
//
//        $tab = str_repeat(' ', 4);
//
//        $output = '';
//
//        if (count($branch->getChildren()) == 0 && count($initValues) == 1) {
//            $output .= $initValues[0];
//        } else {
//            $varNames = array_map(function(Branch $branch) { return $branch->getValue(); }, $vars);
//            $output .= '/* Safely render $' . implode(', $', $this->friendlyVariableNamesForComments($branch, '->')) . ' */' . PHP_EOL;
//            $output .= '(function($' . implode(', $', $varNames) . ') {' . PHP_EOL;
//            $output .= $tab . '$value = $' . $varName . ';' . PHP_EOL;
//            $output .= $tab . 'if ($value === null) return $value;' . PHP_EOL;
//
//            foreach ($branch->getChildren() as $child) {
//                switch ($child->getType()) {
//                    case TemplateParser::T_PROPERTY_OR_FUNC:
//                        $name   = $child->getValue();
//                        $output .= $tab . '// Checking T_PROPERTY_OR_FUNC for possible values' . PHP_EOL;
//                        $output .= $tab . 'if (is_array($value) && array_key_exists(\'' . $name . '\', $value)) $value = $value[\'' . $name . '\'];' . PHP_EOL;
//                        $output .= $tab . 'else if (is_object($value) && method_exists($value, \'' . $name . '\')) $value = $value->' . $name . '();' . PHP_EOL;
//                        $output .= $tab . 'else if (is_object($value) && in_array(\'' . $name . '\', array_keys(get_object_vars($value)))) $value = $value->' . $name . ';' . PHP_EOL;
//                        $output .= $tab . 'else if (function_exists(\'' . $name . '\')) $value = ' . $name . '($value);' . PHP_EOL;
//                        $output .= $tab . 'else return null;' . PHP_EOL;
//
//                        break;
//                    case TemplateParser::T_FUNCTION:
//                        $output   .= $tab . '// Checking T_FUNC for methods' . PHP_EOL;
//                        $funcName = $child->getValue();
//                        $output   .= $tab . 'if (is_object($value) && method_exists($value, \'' . $funcName . '\')) $value = $value->' . $funcName . '(' . $this->renderFunctionArguments($child->getArgs()) . ');' . PHP_EOL;
//                        $output   .= $tab . 'else return null;' . PHP_EOL;
//                        break;
//                    default:
//                        throw new TranspileException('Unknown type: ' . $child->getType());
//                }
//            }
//
//            $output .= $tab . 'return $value;' . PHP_EOL;
//            $output .= '})(' . implode(', ', $initValues) . ')';
//        }
//
//        return $output;
//    }
    public function __construct()
    {
        $this->plugin(new PhpVariable());
        $this->plugin(new PhpCode());
        $this->plugin(new MacroGroupTranspiler(TemplateParser::T_MACRO, [
            new PhpBlockMacroTranspiler(),
            new AuthorMacro(),
            new PropertyMacro(),
            new ControlMacroTranspiler(),
            new ControlOptionMacro(),
            new IfSupportsMacroGroupTranspiler(),
            new WrapMacroParser(),
            new PhpEachMacroTranspiler(),
            new ExtendBlockMacro(),
            new DraggableMacro()
        ], ));
        $this->plugin(new PhpHtmlTranspiler());
    }


}
