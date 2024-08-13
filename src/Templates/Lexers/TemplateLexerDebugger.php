<?php

namespace WebImage\Blocks\Templates\Lexers;

class TemplateLexerDebugger
{
    public static function dump(TemplateLexer $lexer, string $template=null)
    {
        if ($template !== null) $lexer->setInput($template);

        $lexer->reset();
        $lexer->moveNext();
        while ($lexer->lookahead) {
            $lexer->moveNext();
            echo self::tokenDebug($lexer) . '<br/>' . PHP_EOL;
        }
        $lexer->reset();
        $lexer->moveNext();
    }

    public static function tokenDebug(TemplateLexer $lexer, $token = null)
    {
        if ($token === null) $token = $lexer->token;
        if ($token === null) return self::tokenFormat('TOKEN', 'NULL');
        else {
            return self::tokenFormat($token->type, $token->value, $token->position);
        };
    }

    public static function tokenFormat(string $type, string $value, int $position=null): string
    {
        return '<div style="display: inline-flex; margin: 5px 0; border: 1px solid #ccc;">
            <div style="padding: 2px; background-color: #e1e1e1; border-right: 1px solid #ddd;">' . $type . '</div>
            <div style="padding: 2px 5px;">' . htmlentities($value) . '</div>' .
               ($position === null ? '' : '<div style="padding: 2px 5px; color: #666; font-style: italic; border-left: 1px solid #ddd;">' . $position . '</div>')
        . '</div>' . PHP_EOL;
    }
}
