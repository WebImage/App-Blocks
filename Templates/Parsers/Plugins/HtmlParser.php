<?php

namespace WebImage\BlockManager\Templates\Parsers\Plugins;

use WebImage\BlockManager\Templates\Lexers\TemplateLexer;
use WebImage\BlockManager\Templates\Parsers\ParserException;
use WebImage\BlockManager\Templates\Parsers\ParserPluginInterface;
use WebImage\BlockManager\Templates\Parsers\ParserState;
use WebImage\BlockManager\Templates\Lexers\Token;
use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\ParserDebugger;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Parsers\TokenHelper;

class HtmlParser implements ParserPluginInterface
{
    const CONTEXT_HTML_MODE = 'html.mode'; // Used to set context when we are processing just the
    const HTML_MODE_TAG = 'tag'; // Used to set context when we are processing just the attribute string and not the tag.
    const HTML_MODE_ATTRIBUTE = 'attribute'; // Used to set context when we are processing just the attribute string and not the tag.
    const T_HTML_TAG = 'T_HTML_TAG';
    const T_HTML_DYNAMIC_TAG = 'T_HTML_DYNAMIC_TAG';
    const T_HTML_TAG_NAME = 'T_HTML_TAG_NAME';
    const T_HTML_CLOSE_TAG = 'T_HTML_CLOSE_TAG';
    const T_HTML_OPENCLOSE_TAG = 'T_HTML_OPENCLOSE_TAG';
    const T_HTML_ATTRIBUTE = 'T_HTML_ATTRIBUTE';

    public function canParseText(ParserState $state): bool
    {
        // Check that we have an < but not followed by ?  - which would be PHP?
        return $state->lexer->token->isA($state->lexer::T_OPEN_ANGLE_BRACKET) && !$state->lexer->isNextToken($state->lexer::T_QUESTION);
    }

    public function parseText(ParserState $state, array $untilBranchTypes = null): ?Branch
    {
        return ParserDebugger::wrapBranch(function () use ($state, $untilBranchTypes) {
            $state->meta['depth'] = ($state->meta['depth'] ?? 0) + 1;
            $lexer                = $state->lexer;

            $tagName  = null;
            $tagType  = self::T_HTML_TAG;
            $attrName = null;
            $children = [];

            if ($lexer->lookahead && $lexer->lookahead->type == TemplateLexer::T_FORWARD_SLASH) {
                $tagType = self::T_HTML_CLOSE_TAG;
            }

            $args                   = [];
            $didFindUntilBranchType = $untilBranchTypes === null;

            while ($lexer->lookahead) {
                $lexer->moveNext();
                switch ($lexer->token->type) {

                    case TemplateLexer::T_CLOSE_ANGLE_BRACKET:
                        if ($tagType === self::T_HTML_TAG) {
                            $children = $state->parser->parseText([self::T_HTML_CLOSE_TAG]);
                            array_pop($children); // Do not keep T_HTML_CLOSE_TAG as part of children
                        } else if ($tagType === self::T_HTML_ATTRIBUTE) {
                            die('Investigate: ' . __FILE__ . ':' . __LINE__);
                        }
                        break 2;

                    case TemplateLexer::T_STRING:
                        if ($tagName === null && $this->getHtmlMode($state) === self::HTML_MODE_TAG) $tagName = $lexer->token->value;
                        else if ($attrName === null) {
                            $attrName = $lexer->token->value;
                        }
                        break;

                    case TemplateLexer::T_EQUAL_SIGN:
                    case TemplateLexer::T_WHITESPACE:
                    case TemplateLexer::T_NEWLINE:
                        break;

                    case TemplateLexer::T_SINGLE_QUOTE:
                    case TemplateLexer::T_DOUBLE_QUOTE:
                        if ($attrName === null) {
                            throw new ParserException('Unexpected ' . $lexer->token->type . ' before attribute name is set');
                        }

                        $arg = new Branch(
                            self::T_HTML_ATTRIBUTE,
                            $attrName,
                            [],
                            [
                                $state->parser->parseString($lexer->token->type)
                            ]
                        );

                        if ($this->getHtmlMode($state) == self::HTML_MODE_ATTRIBUTE) {
                            $state->meta['depth'] = $state->meta['depth'] - 1;
                            return $arg;
                        }

                        $args[]   = $arg;
                        $attrName = null;
                        break;

                    case TemplateLexer::T_OPEN_BRACKET:
                        if ($this->getHtmlMode($state) == self::HTML_MODE_TAG && $tagName === null) { // Dyanic tag name
                            $dynamic = $state->parser->parseCode(TemplateLexer::T_CLOSE_BRACKET);
                            $tagType = self::T_HTML_DYNAMIC_TAG;
                            $tagName = 'dynamic';
                            $args[] = new Branch(self::T_HTML_TAG_NAME, null, [$dynamic]);
                            break;
                        } else if ($attrName === null) {
                            // @TODO Possibly add support for variable based parameter names?
                            throw new ParserException('Unexpected ' . TemplateLexer::T_OPEN_BRACKET . ' before attribute name is set');
                        }


                        $arg = new Branch(
                            self::T_HTML_ATTRIBUTE,
                            $attrName,
                            [],
                            [
                                $state->parser->parseCode(TemplateLexer::T_CLOSE_BRACKET)
                            ]
                        );
                        if ($this->getHtmlMode($state) == self::HTML_MODE_ATTRIBUTE) {
                            $state->meta['depth'] = $state->meta['depth'] - 1;
                            return $arg;
                        }
                        $args[]   = $arg;
                        $attrName = null;
                        break;

                    case TemplateLexer::T_FORWARD_SLASH:
                        if ($lexer->lookahead) {
                            if ($lexer->lookahead->type == TemplateLexer::T_CLOSE_ANGLE_BRACKET) {
                                $lexer->moveNext();
                                $tagType = self::T_HTML_OPENCLOSE_TAG;
                                break 2;
                            } else {
                                break;
                            }
                        } else {
                            throw new ParserException('Expecting ' . TemplateLexer::T_CLOSE_ANGLE_BRACKET . ' after ' . TemplateLexer::T_FORWARD_SLASH . ', but found end of input');
                        }

                    default:
                        // Check plugins for support for the unknown token
                        foreach ($state->parser->plugins() as $plugin) {
                            if ($plugin->canParseText($state)) {
                                // Pass parsing to plugin, but delegate child parsing back to this plugin / HtmlParser
                                $branch = $state->delegateParsing(
                                    $this,
                                    function (ParserState $state) use ($plugin) {
                                        return $plugin->parseText($state);
                                        // When parsing is passed back to HtmlParser it will be at a different depth, so set HTML_MODE so that we know we are processing attributes and not a full HTML tag
                                    },
                                    [self::CONTEXT_HTML_MODE => self::HTML_MODE_ATTRIBUTE]
                                );

                                // Check the result and return if $branch matches the type we are looking for ($untilBranchTypes)
                                if ($branch !== null) {
                                    if ($untilBranchTypes !== null && in_array($branch->getType(), $untilBranchTypes)) {
                                        $state->meta['depth'] = $state->meta['depth'] - 1;
                                        return $branch;
                                    }

                                    $args[] = $branch;
                                }

                                break 2;
                            }
                        }
                        // If we made it this far then we are dealing with an unknown token
                        throw new ParserException('Unexpected token ' . $lexer->token->type . ' in HTML tag');
                }
            }

            if ($state->lexer->token->type != $lexer::T_CLOSE_ANGLE_BRACKET) throw new ParserException('Expecting ' . $lexer::T_CLOSE_ANGLE_BRACKET . ', but found ' . $lexer->token->type);

            return new Branch($tagType, $tagName, $children, $args);
        }, $state);
    }

    private function getHtmlMode(ParserState $state): string
    {
        return isset($state->context[self::CONTEXT_HTML_MODE]) ? $state->context[self::CONTEXT_HTML_MODE] : self::HTML_MODE_TAG;
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
