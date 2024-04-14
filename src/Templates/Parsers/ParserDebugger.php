<?php

namespace WebImage\BlockManager\Templates\Parsers;

use WebImage\BlockManager\Templates\Lexers\TemplateLexer;
use WebImage\BlockManager\Templates\Lexers\TemplateLexerDebugger;

class ParserDebugger
{
    static $ENABLED       = false;
    static $CAPTURE_ERROR = true;

    public static function dumpTree(Branch $branch, $depth = 0)
    {
        echo str_repeat('&nbsp;', 5 * $depth);
        echo $branch->getType() . ($branch->getValue() === null ? '' : ' (' . $branch->getValue() . ')') . '<br/>' . PHP_EOL;

        foreach ($branch->getChildren() as $child) {
            self::dumpTree($child, $depth + 1);
        }
    }

    public static function wrapBranch(callable $callback, ?ParserState $state = null): ?Branch
    {
        if (!self::$ENABLED) return call_user_func($callback);
        $initState = self::debugState($state);

        ob_start();
        try {
            $result = call_user_func($callback);
        } catch (\Exception $e) {
            if (self::$CAPTURE_ERROR) {
                echo 'Error: ' . self::highlight($e->getMessage(), '#fcc') . '<br/>' . PHP_EOL;
                $result = null;
            } else {
                throw $e;
            }
        }
        $content = ob_get_contents();
        ob_end_clean();

//        if ($result === null) {
//            self::$CAPTURE_ERROR = false;
//            throw new \Exception('Result is null');
//        }
        $branchDescription = $result === null ? 'NONE' : $result->getType();
        $branchValue       = $result === null ? 'NONE' : (is_string($result->getValue()) ? $result->getValue() : gettype($result));
        echo self::OPEN;
        echo 'Start Branch: ' . self::highlight($branchDescription) . ': ' . $branchValue . '; ' . $initState . '<br>';
//        echo self::OPEN;
        echo $content;
//        echo self::CLOSE;
        echo 'End Branch: ' . self::highlight($branchDescription) . '; ' . self::debugState($state) . '<br>';
        echo self::CLOSE;
        return $result;
    }

    public static function wrapArray(callable $callback, ?ParserState $state = null): array
    {
        if (!self::$ENABLED) return call_user_func($callback);
        $initState = self::debugState($state);

        ob_start();
        try {
            $result = call_user_func($callback);
        } catch (\Exception $e) {
            if (self::$CAPTURE_ERROR) {
                echo 'Error: ' . self::highlight($e->getMessage(), '#fcc') . '<br/>' . PHP_EOL;
                $result = [];
            } else {
                throw $e;
            }
        }
        $contents = ob_get_contents();
        ob_end_clean();

        echo self::OPEN;
        echo 'START ARRAY: ' . count($result) . '; ' . $initState . '<br/>' . PHP_EOL;
//        echo self::OPEN;
        echo $contents;
//        echo self::CLOSE;
        echo 'END ARRAY: ' . count($result) . '; ';
        foreach ($result as $branch) {
            echo self::highlight($branch === null ? 'NONE' : $branch->getType(), '#cfc') . ' ';
        }
        echo '; ' . self::debugState($state);
        echo '<br/>' . PHP_EOL;
        echo self::CLOSE;
        return $result;
    }

    private static function highlight(string $text, string $backgroundColor = '#ff0', $foregroundColor = '#000'): string
    {
        return '<span style="background-color: ' . $backgroundColor . '; color: ' . $foregroundColor . '">' . $text . '</span>';
    }

    private static function debugState(ParserState $state = null): string
    {
        if ($state === null) return self::highlight('No state provided', '#f00', '#fff');

        $titleStyle = 'font-size: 9px; line-height: 9px; display: none';
        return '<div style="display: inline-flex; justify-self: center; gap: 20px; border: 1px solid #ccc; padding: 0 10px; ">' .
               '<div>Cur: ' . TemplateLexerDebugger::tokenDebug($state->lexer, $state->lexer->token) . '</div>' .
               '<div>Next: ' . TemplateLexerDebugger::tokenDebug($state->lexer, $state->lexer->lookahead) . '</div>' .
               '<div>Prev: ' . TemplateLexerDebugger::tokenDebug($state->lexer, $state->lexer->peekBehind()) . '</div>' .
               '</div>';
    }

    public static function echo(string $string, ?ParserState $state=null): void
    {
        if (!self::$ENABLED) return;
        echo '<span style="font-style: italic;">' . $string . '</span> ' . self::debugState($state) . '<br/>' . PHP_EOL;
    }

    /**
     * Show the rest of the lexer tokens to be parsed
     * @param TemplateLexer $lexer
     * @return string
     */
    public static function drainLexer(TemplateLexer $lexer): string
    {
        echo $lexer->token->type . ' - ' . htmlentities($lexer->token->value) . '<br/>' . PHP_EOL;
        while ($lexer->lookahead) {
            $lexer->moveNext();
            echo $lexer->token->type . ' - ' . htmlentities($lexer->token->value) . '<br/>' . PHP_EOL;
        }
        die(__FILE__ . ':' . __LINE__ . '<br />' . PHP_EOL);
    }

    const OPEN = '<div style="border: 1px solid #000; padding: 10px 20px; margin: 10px 0;">' . PHP_EOL;
    const CLOSE = '</div>' . PHP_EOL;
}
