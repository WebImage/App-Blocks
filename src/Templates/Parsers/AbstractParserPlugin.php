<?php

namespace WebImage\Blocks\Templates\Parsers;

abstract class AbstractParserPlugin implements ParserPluginInterface
{
    public function canParseText(ParserState $state): bool
    {
        return false;
    }

    public function parseText(ParserState $state, array $untilBranchTypes = null): ?Branch
    {
        return null;
    }

    public function canParseCode(ParserState $state): bool
    {
        return false;
    }

    public function parseCode(ParserState $state): ?Branch
    {
        return null;
    }
}
