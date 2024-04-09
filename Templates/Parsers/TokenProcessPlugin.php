<?php

namespace WebImage\BlockManager\Templates\Parsers;

interface TokenProcessPlugin
{
    public function canParseText(ParserState $state);
    public function parseText(ParserState $state);
}
