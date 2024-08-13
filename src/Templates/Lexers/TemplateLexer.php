<?php

namespace WebImage\Blocks\Templates\Lexers;

//use Doctrine\Common\Lexer\AbstractLexer;

class TemplateLexer extends AbstractLexer
{
    const T_OPEN_BRACKET = 'T_OPEN_BRACKET';
    const T_CLOSE_BRACKET = 'T_CLOSE_BRACKET';
    const T_OPEN_ANGLE_BRACKET = 'T_OPEN_ANGLE_BRACKET';
    const T_CLOSE_ANGLE_BRACKET = 'T_CLOSE_ANGLE_BRACKET';
    const T_OPEN_PAREN = 'T_OPEN_PAREN';
    const T_CLOSE_PAREN = 'T_CLOSE_PAREN';
    const T_OPEN_SQUARE_BRACKET = 'T_OPEN_SQUARE_BRACKET';
    const T_CLOSE_SQUARE_BRACKET = 'T_CLOSE_SQUARE_BRACKET';
    const T_STRING = 'T_STRING';
    const T_WHITESPACE = 'T_WHITESPACE';
    const T_EXCLAMATION = 'T_EXCLAMATION';
    const T_AT = 'T_AT';
    const T_NUMBER_SIGN = 'T_NUMBER_SIGN';
    const T_DOLLAR_SIGN = 'T_DOLLAR_SIGN';
    const T_PERCENT_SIGN = 'T_PERCENT_SIGN';
    const T_CARET = 'T_CARET';
    const T_AMPERSAND = 'T_AMPERSAND';
    const T_ASTERISK = 'T_ASTERISK';
    const T_PERIOD = 'T_PERIOD';
    const T_DOUBLE_QUOTE = 'T_DOUBLE_QUOTE';
    const T_SINGLE_QUOTE = 'T_SINGLE_QUOTE';
    const T_SEMI_COLON = 'T_SEMI_COLON';
    const T_COLON = 'T_COLON';
    const T_COMMA = 'T_COMMA';
    const T_QUESTION = 'T_QUESTION';
    const T_BACKSLASH = 'T_BACKSLASH';
    const T_FORWARD_SLASH = 'T_FORWARD_SLASH';
    const T_PLUS = 'T_PLUS';
    const T_DASH = 'T_MINUS';
    const T_EQUAL_SIGN = 'T_EQUAL_SIGN';
    const T_NEWLINE = 'T_NEWLINE';

    protected function getCatchablePatterns()
    {
        return [
            '[a-zA-Z_][a-zA-Z0-9_]*',
            '-?[0-9]+?[\.0-9]*',
            "\r", "\n",
            '[\s]+'
        ];
    }

    protected function getNonCatchablePatterns()
    {
        return [];
    }

    protected function getType(&$value)
    {
        if ($value == '{') return self::T_OPEN_BRACKET;
        else if ($value == '}') return self::T_CLOSE_BRACKET;
        else if ($value == "\r") return null;
        else if ($value == "\n") return self::T_NEWLINE;
        else if (strlen($value) > 0 && strlen(trim($value)) == 0) return self::T_WHITESPACE;
        else if ($value == '@') return self::T_AT;
        else if ($value == '!') return self::T_EXCLAMATION;
        else if ($value == '#') return self::T_NUMBER_SIGN;
        else if ($value == '$') return self::T_DOLLAR_SIGN;
        else if ($value == '%') return self::T_PERCENT_SIGN;
        else if ($value == '^') return self::T_CARET;
        else if ($value == '&') return self::T_AMPERSAND;
        else if ($value == '*') return self::T_ASTERISK;
        else if ($value == '.') return self::T_PERIOD;
        else if ($value == '"') return self::T_DOUBLE_QUOTE;
        else if ($value == "'") return self::T_SINGLE_QUOTE;
        else if ($value == '(') return self::T_OPEN_PAREN;
        else if ($value == ')') return self::T_CLOSE_PAREN;
        else if ($value == '[') return self::T_OPEN_SQUARE_BRACKET;
        else if ($value == ']') return self::T_CLOSE_SQUARE_BRACKET;
        else if ($value == ':') return self::T_COLON;
        else if ($value == ';') return self::T_SEMI_COLON;
        else if ($value == ',') return self::T_COMMA;
        else if ($value == '?') return self::T_QUESTION;
        else if ($value == '<') return self::T_OPEN_ANGLE_BRACKET;
        else if ($value == '>') return self::T_CLOSE_ANGLE_BRACKET;
        else if ($value == '/') return self::T_FORWARD_SLASH;
        else if ($value == '\\') return self::T_BACKSLASH;
        else if ($value == '+') return self::T_PLUS;
        else if ($value == '-') return self::T_DASH;
        else if ($value == '=') return self::T_EQUAL_SIGN;

        else return self::T_STRING;
    }
}
