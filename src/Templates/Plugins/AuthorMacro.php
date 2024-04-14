<?php

namespace WebImage\BlockManager\src\Templates\Plugins;

use WebImage\BlockManager\src\Templates\Parsers\Branch;
use WebImage\BlockManager\src\Templates\Parsers\BranchArgumentDefinition;
use WebImage\BlockManager\src\Templates\Parsers\ParserException;
use WebImage\BlockManager\src\Templates\Parsers\ParserState;
use WebImage\BlockManager\src\Templates\Parsers\Plugins\AbstractMacroParser;
use WebImage\BlockManager\src\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\src\Templates\Transpilers\Plugins\TranspilerPluginTrait;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\BlockManager\src\Templates\Transpilers\TranspilerState;

class AuthorMacro extends AbstractMacroParser implements TranspilerPluginInterface
{
    use TranspilerPluginTrait;

    const MACRO_AUTHOR = 'author';
    const META_AUTHOR_EMAIL = 'block.author.email';
    const META_AUTHOR_NAME = 'block.author.name';
    protected array $supportedMacros = [self::MACRO_AUTHOR];

    protected function processArguments(ParserState $state, array $args)
    {
        $meta      = $this->currentState->meta;
        $context   = $this->currentState->context;
        $macroName = $context[self::CONTEXT_MACRO_NAME];
        $email     = self::getArgumentStringByName($macroName, $args, 'email');
        $name      = self::getArgumentStringByName($macroName, $args, 'name');


        if (!preg_match('/.+@.+\..+/', $email)) throw new ParserException($macroName . ' first parameter must be a valid email address');

        $meta[self::META_AUTHOR_EMAIL] = $email;
        $meta[self::META_AUTHOR_NAME]  = $name;
    }

    public static function getArgumentDefinitions(string $macroName): array
    {
        return [
            new BranchArgumentDefinition('email', 'The email address of the author.', false),
            new BranchArgumentDefinition('name', 'The name of the author.', false)
        ];
    }

    public function canTranspile(TranspilerState $state, Branch $branch): bool
    {
        return $branch->getType() == TemplateParser::T_MACRO && $branch->getValue() == 'author';
    }

    public function transpile(TranspilerState $state, Branch $branch): string
    {
        return '';
    }
}
