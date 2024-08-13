<?php

namespace WebImage\Blocks\Templates\Transpilers\Plugins;

use WebImage\Blocks\Processing\ReactVariableTypes;
use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Parsers\Plugins\WrapMacroParser;
use WebImage\Blocks\Templates\Plugins\DraggableMacro;
use WebImage\Blocks\Templates\Plugins\PropertyMacro;
use WebImage\Blocks\Templates\Transpilers\TranspileException;
use WebImage\Blocks\Templates\Transpilers\TranspilerState;

class ReactBlockMacroTranspiler extends BlockMacroTranspiler
{
    protected function renderBlockHeader(TranspilerState $state, Branch $branch): string
    {
        $meta = $state->getMeta();
        $class = $meta['block.class'];
        $draggable = false;

        $knownBlockKeys = ['name', 'class', 'label', 'wrap', 'author.email', 'author.name', 'extend', 'draggable', 'properties'];
        $imports = 'import react, {FunctionComponent} from \'React\'' . PHP_EOL;
        if (isset($meta[WrapMacroParser::META_BLOCK_WRAP])) {
            foreach($meta[WrapMacroParser::META_BLOCK_WRAP] as $wrap) {
                $imports .= 'import ' . $wrap . ' from \'' . $wrap . '\';' . PHP_EOL;
            }
        }

//        if (isset($meta[ExtendBlockMacroParser::META_BLOCK_EXTEND])) {
//            die(__FILE__ . ':' . __LINE__ . '<br />' . PHP_EOL);
//        }

        if (isset($meta[DraggableMacro::META_DRAGGABLE]) && $meta[DraggableMacro::META_DRAGGABLE] === true) {
            $draggable = true;
        }

        foreach($meta->keys() as $key) {
            if (!preg_match('/block\.(.+)/', $key, $matches)) continue;
            list($_, $subkey) = $matches;

            if (!in_array($subkey, $knownBlockKeys)) {
                echo 'Unknown key: ' . $key . '<br/>' . PHP_EOL;
                die(__FILE__ . ':' . __LINE__ . '<br />' . PHP_EOL);
            }
        }
        $draggableCode = $draggable ? '/* Draggable */' . PHP_EOL : '';
        $blockTypeDefinition = $this->createBlockTypeDefinition($state, $class, $state);
        $blockProps = empty($blockTypeDefinition) ? '' : '<' . $this->getBlockTypeDefinitionName($class) . '>';

        return <<<EOT
/** Start Imports */
$imports
/** Draggable Code */
$draggableCode
/** Block Type Definition */
$blockTypeDefinition
/** Class */
const {$class}: FunctionComponent<BlockProps$blockProps> = (props) => {

EOT;
    }

    protected function createBlockTypeDefinition(TranspilerState $state, string $class): string
    {
        $output = '';
        $meta = $state->getMeta();

        if (isset($meta[PropertyMacro::META_PROPERTIES])) {
            $typeName = $this->getBlockTypeDefinitionName($class);
            $output .= 'type ' . $typeName . ' = {' . PHP_EOL;
            foreach($meta[PropertyMacro::META_PROPERTIES] as $key => $def) {
                $output .= $state->getTranspiler()->indent($key . ': ' . ReactVariableTypes::getReactType($def['type'])) . PHP_EOL;
            }
            $output .= '}' . PHP_EOL;
        }

        return $output;
    }

//    protected function getJavascriptTypeFromPropertyType(string $type): string
//    {
//        switch($type) {
//            case PropertyMacro::TYPE_INT:
//                return 'number';
//            case PropertyMacro::TYPE_STRING:
//                return 'string';
//            default:
//                throw new TranspileException('Unsupported property type: ' . $type);
//        }
//    }

    protected function getBlockTypeDefinitionName(string $class): string
    {
        return sprintf('%sBlock', $class);
    }

    protected function renderBlockBody(TranspilerState $state, Branch $branch): string
    {
        $body = parent::renderBlockBody($state, $branch);
        $reactTag = 'React.Fragment';

        return $state->getTranspiler()->indent('return (') . PHP_EOL .
               $state->getTranspiler()->indent('<' . $reactTag . '>', 2) . PHP_EOL .
               $state->getTranspiler()->indent($body, 3) . PHP_EOL .
               $state->getTranspiler()->indent('</' . $reactTag . '>', 2) . PHP_EOL .
               $state->getTranspiler()->indent(');') . PHP_EOL;
//        return 'BLOCK' . '<br/>' . PHP_EOL;;
    }

    protected function renderBlockFooter(TranspilerState $state, Branch $branch): string
    {
        return '}' . PHP_EOL;
    }
}
