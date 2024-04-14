<?php

namespace WebImage\BlockManager\src\Templates;

class IndentHelper {
    public static function indent(string $text, string $indentChars, int $depth=0): string {
        $indent = str_repeat($indentChars, $depth);
        return $indent . preg_replace_callback('/\r?\n/', function($match) use ($indent) {
            return $match[0] . $indent;
        }, $text);
    }
}
