<?php

namespace WebImage\Blocks\Templates\Parsers;

interface TokenProcessPlugin
{
    public function canParseText(ParserState $state);
    public function parseText(ParserState $state);
}
