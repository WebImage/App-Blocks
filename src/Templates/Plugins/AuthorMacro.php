<?php

namespace WebImage\Blocks\Templates\Plugins;

use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Parsers\BranchArgumentDefinition;
use WebImage\Blocks\Templates\Parsers\ParserException;
use WebImage\Blocks\Templates\Parsers\ParserState;
use WebImage\Blocks\Templates\Parsers\Plugins\AbstractMacroParser;
use WebImage\Blocks\Templates\Parsers\TemplateParser;
use WebImage\Blocks\Templates\Transpilers\Plugins\TranspilerPluginTrait;
use WebImage\Blocks\Templates\Transpilers\TranspilerPluginInterface;
use WebImage\Blocks\Templates\Transpilers\TranspilerState;

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
