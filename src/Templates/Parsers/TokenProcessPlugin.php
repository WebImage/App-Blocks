<?php

namespace WebImage\BlockManager\src\Templates\Parsers;

interface TokenProcessPlugin
{
    public function canParseText(ParserState $state);
    public function parseText(ParserState $state);
}
