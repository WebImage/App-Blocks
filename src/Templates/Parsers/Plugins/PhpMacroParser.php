<?php

namespace WebImage\Blocks\Templates\Parsers\Plugins;

use WebImage\Blocks\Templates\Lexers\TemplateLexer;
use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Parsers\ParserState;
use WebImage\Blocks\Templates\Parsers\TemplateParser;

class PhpMacroParser extends AbstractMacroParser
{
    protected array $supportedMacros = ['php']; // @endphp is capture automatically

//    public function parseText(ParserState $state, array $untilBranchTypes = null): ?Branch
//    {
//        $code = $this->getCode($state);
//
//         return IfSupportsMacroParser::createIf([
//             IfSupportsMacroParser::createIfCondition(['php'], [
//                 new Branch(TemplateParser::T_STRING, $code->getValue())
//             ])
//             ]);
//    }
    protected function createBranch(ParserState $state, string $type, string $name, array $children = [], array $args = [], array $meta = []): ?Branch
    {
        return IfSupportsMacroParser::createIf([
                                                   IfSupportsMacroParser::createIfCondition(['php'], $children)
                                               ]);
    }

    protected function processBody(ParserState $state, string $macroName): array
    {
        $php = $this->getCode($state);

        return [$php];
    }

    private function getCode(ParserState $state): Branch
    {
        $lexer   = $state->lexer;
        $capture = '';

        while ($lexer->lookahead) {
            $lexer->moveNext();
            if ($lexer->token->isA(TemplateLexer::T_AT) && $lexer->isNextToken(TemplateLexer::T_STRING) && $lexer->lookahead->value == 'endphp') {
                $lexer->moveNext();
                break;
            }
            $capture .= $lexer->token->value;
        }

        return new Branch(TemplateParser::T_STRING, $capture);
    }
}
