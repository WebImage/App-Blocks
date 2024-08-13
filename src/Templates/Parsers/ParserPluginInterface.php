<?php

namespace WebImage\Blocks\Templates\Parsers;

interface ParserPluginInterface
{
    public function canParseText(ParserState $state): bool;

    /**
     * @param ParserState $state
* //     * @param ParserPluginInterface|null $plugin Can be specified if plugin should capture
     * @return \WebImage\Blocks\Templates\Parsers\Branch|null
     */
    public function parseText(ParserState $state, array $untilBranchTypes=null): ?Branch;

    public function canParseCode(ParserState $state): bool;

    public function parseCode(ParserState $state): ?Branch;
}
