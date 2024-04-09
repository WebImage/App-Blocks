<?php

namespace WebImage\BlockManager\Templates\Parsers;

use WebImage\BlockManager\Templates\Parsers\ParserPluginInterface;
use WebImage\BlockManager\Templates\Parsers\ParserState;

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
