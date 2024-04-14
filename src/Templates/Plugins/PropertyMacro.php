<?php

namespace WebImage\BlockManager\Templates\Plugins;

use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\BranchArgumentDefinition;
use WebImage\BlockManager\Templates\Parsers\ParserException;
use WebImage\BlockManager\Templates\Parsers\ParserState;
use WebImage\BlockManager\Templates\Parsers\Plugins\AbstractMacroParser;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Parsers\VariableTypes;
use WebImage\BlockManager\Templates\Transpilers\Plugins\TranspilerPluginTrait;
use WebImage\BlockManager\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\Templates\Transpilers\TranspilerState;

class PropertyMacro extends AbstractMacroParser implements TranspilerPluginInterface
{
    use TranspilerPluginTrait;
    const MACRO_PROPERTY = 'property';
//    const META_BLOCK_PROPERTIES = 'block.properties';
    const META_PROPERTIES = 'properties';

    protected array $supportedMacros = [self::MACRO_PROPERTY];

    public static function getArgumentDefinitions(string $macroName): array
    {
        return [
            new BranchArgumentDefinition('name', 'The name of a block property.'),
            new BranchArgumentDefinition('type', 'The property type, e.g. int, string, float.'),
            new BranchArgumentDefinition('default', 'The default value for the property.')
        ];
    }

    protected function processArguments(ParserState $state, array $args)
    {
        $name = self::getArgumentStringByName($this->getMacroName(), $args, 'name');
        $type = self::getArgumentStringByName($this->getMacroName(), $args, 'type');
        $default = self::getArgumentValueByName($this->getMacroName(), $args, 'default');

        $metaProperties = $state->meta[self::META_PROPERTIES] ?? [];

        if (isset($metaProperties[$name])) {
            throw new ParserException('Only one property can be defined for ' . $name);
        }

        $metaProperties[$name] = [
            'type' => VariableTypes::validType($type),
            'default' => $default
        ];

        $state->meta[self::META_PROPERTIES] = $metaProperties;
    }

    private function getValidTypes(): array
    {
        return [
            self::TYPE_INT,
            self::TYPE_STRING,
            self::TYPE_FLOAT
        ];
    }

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_MACRO && $branch->getValue() == self::MACRO_PROPERTY;
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
        return '';
    }
}
