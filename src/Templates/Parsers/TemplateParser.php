<?php

namespace WebImage\BlockManager\Templates\Parsers;

use WebImage\BlockManager\Templates\Context;
use WebImage\BlockManager\Templates\Lexers\TemplateLexer;
use WebImage\BlockManager\Templates\Lexers\TemplateLexerDebugger;
use WebImage\BlockManager\Templates\Meta;
use WebImage\BlockManager\Templates\Parsers\ParseException;

class TemplateParser
{
	const T_ROOT = 'T_ROOT';
	const T_LITERAL = 'T_LITERAL';
	const T_STRING = 'T_STRING';
	const T_NUMBER = 'T_NUMBER';
	const T_VARIABLE = 'T_VARIABLE';
	const T_PROPERTY_OR_FUNC = 'T_PROPERTY_OR_FUNC';
	const T_FUNCTION = 'T_FUNCTION';
	const T_MACRO = 'T_MACRO';
    const T_CHILDREN = 'T_CHILDREN';
    const T_IF = 'T_IF';
    const T_OBJECT = 'T_OBJECT';
    const T_KEY_VALUE_PAIR = 'T_KEY_VALUE_PAIR';
    const T_CODE = 'T_CODE';
    const T_BLOCK_COMMENT = 'T_BLOCK_COMMENT';
    const T_INLINE_COMMENT = 'T_INLINE_COMMENT';

    const META_SAFETY_CHECKS = '_safetyChecks';
    const META_QUOTE_TYPE = '_quoteType';

    private TemplateLexer $lexer;
    /**
     * @var array ParserPlugin
     */
    private array   $plugins = [];
    private Meta    $meta;
    private Context $context;

    public function __construct(TemplateLexer $lexer)
    {
        $this->lexer   = $lexer;
    }

    public function parse(string $string): ParseResult
    {
        $this->reset();
        $lexer = $this->lexer;
        $lexer->setInput($string);
        $lexer->moveNext();

        $root = new Branch(self::T_ROOT, null, $this->parseText());

        return new ParseResult($root, $this->meta);
    }

    private function reset(): void
    {
        $this->meta    = new Meta();
        $this->context = new Context();
    }

    public function plugin(ParserPluginInterface $plugin)
    {
        $this->plugins[] = $plugin;
    }

    /**
     * @return \WebImage\BlockManager\Templates\Parsers\ParserPluginInterface[]
     */
    public function plugins(): array
    {
        return $this->plugins;
    }

    /**
     * @param string[] $untilBranchTypes
     * @return Branch[]
     */
    public function parseText(array $untilBranchTypes = null): array
    {
        $state = new ParserState($this, $this->lexer, $this->meta, $this->context);
        return ParserDebugger::wrapArray(function () use ($untilBranchTypes, $state) {
            ParserDebugger::echo('TemplateParser.parseText(until = ' . ($untilBranchTypes === null ? 'END' : implode(', ', $untilBranchTypes)) . ')', $state);
            $lexer = $this->lexer;
            /** @var Branch[] $branches */
            $branches       = [];
            $wasEndTypeFound = $untilBranchTypes === null;

            while ($lexer->lookahead) {
                $delegateParser = $state->getDelegateParser();
                if ($delegateParser !== null) {
                    ParserDebugger::echo('TemplateParser.delegateParser: ' . get_class($delegateParser), $state);
                    $addBranch = $delegateParser->parseText($state, $untilBranchTypes);
                    ParserDebugger::echo('TemplateParser.delegateParser.result: ' . ($addBranch === null ? 'NULL' : $addBranch->getType()), $state);
                    goto check_branch;
                }

                $lexer->moveNext();

                /** @var Branch $addBranch */
                $addBranch = null;
                $debugType = $lexer->token->type;

                foreach ($this->plugins as $plugin) {
                    if (!($plugin instanceof ParserPluginInterface) || !$plugin->canParseText($state)) continue;
                    $addBranch = $plugin->parseText($state);
                    if ($addBranch === null) ParserDebugger::echo('Plugin ' . get_class($plugin) . ' can parse state, but NULL returned', $state);
                    goto check_branch;
                }

                switch ($lexer->token->type) {
                    case TemplateLexer::T_OPEN_BRACKET:
                        $addBranch = $this->parseBracket();
                        break;

                    case TemplateLexer::T_FORWARD_SLASH:
                        $addBranch = $this->parseComment();
                        if ($addBranch !== null) break;

                    default:
                        if (count($branches) == 0 || $branches[count($branches) - 1]->getType() != self::T_LITERAL) {
                            $addBranch = ParserDebugger::wrapBranch(function () use ($lexer, $state) {
                                ParserDebugger::echo('Text: ' . $lexer->token->value, $state);
                                return new Branch(self::T_LITERAL, $lexer->token->value);
                            }, $state);
                        } else {
                            $prevBranch = $branches[count($branches) - 1];
                            // Replace previous
                            $branches[count($branches) - 1] = ParserDebugger::wrapBranch(function () use ($prevBranch, $lexer, $state) {
                                ParserDebugger::echo('Appending text: ' . $lexer->token->value, $state);
                                return new Branch($prevBranch->getType(), $prevBranch->getValue() . $lexer->token->value, $prevBranch->getChildren(), $prevBranch->getArgs(), $prevBranch->getMeta()->toArray());
                            }, $state);
                        }
                }

                check_branch:

                if ($addBranch !== null) {
                    if (!($addBranch instanceof Branch)) {
                        throw new ParserException($debugType . ' did not return Branch');
                    }

                    $branches[] = $addBranch;

                    if ($untilBranchTypes !== null && in_array($addBranch->getType(), $untilBranchTypes)) {
                        $wasEndTypeFound = true;
                        break;
                    }
                }
            }

            /**
             * Check if the expected end type was found... or if NULL exists within $untilBranchTypes then allow parser to capture ALL remaining input
             */
            if (!$wasEndTypeFound && !in_array(null, $untilBranchTypes)) {
                throw new ParserException('Reached end of input without finding type ' . implode(', ', $untilBranchTypes));
            }

            return $branches;
        }, $state);
    }

    private function parseBracket(): ?Branch
    {
        $lexer = $this->lexer;
        if ($lexer->token->type != TemplateLexer::T_OPEN_BRACKET) throw new ParserException('Expecting ' . TemplateLexer::T_OPEN_BRACKET . ', but found end of string');

        return $this->parseCode(TemplateLexer::T_CLOSE_BRACKET);
    }

    public function parseCode(string $untilType): ?Branch
    {
        $lexer    = $this->lexer;
        $branches = [];

        while ($lexer->lookahead) {
            $lexer->moveNext();

            switch ($lexer->token->type) {
                case $untilType:
                    break 2;

                case TemplateLexer::T_SINGLE_QUOTE:
                case TemplateLexer::T_DOUBLE_QUOTE:
                    if (count($branches) > 0 && $previousType != TemplateLexer::T_PLUS) throw new ParserException('Unexpected ' . $lexer->token->type . '. Missing ' . TemplateLexer::T_PLUS . ' separator in code');
                    $branches[] = $this->parseString($lexer->token->type);
                    break;

                case TemplateLexer::T_WHITESPACE:
                case TemplateLexer::T_NEWLINE:
                    continue 2;

                case TemplateLexer::T_PLUS:
                    if ($previousType == TemplateLexer::T_PLUS) throw new ParserException('Unexpected ' . TemplateLexer::T_PLUS);
                    break;

                case TemplateLexer::T_STRING:
                    if (count($branches) > 0 && $previousType != TemplateLexer::T_PLUS) throw new ParserException('Unexpected ' . $lexer->token->type . '. Missing ' . TemplateLexer::T_PLUS . ' separator in code');
                    $branches[] = $this->parseVariable();
                    break;

                case TemplateLexer::T_OPEN_BRACKET:
//                    ParserDebugger::drainLexer($lexer);
                    $branches[] = $this->parseObject();
//                    echo '<pre>';print_r($branches[count($branches)-1]); die(__FILE__ . ':' . __LINE__ . PHP_EOL);
                    break;

                default:
                    throw new ParserException('Unexpected token ' . $lexer->token->type);
            }
            $previousType = $lexer->token->type;
        }

        return new Branch(self::T_CODE, null, $branches);
    }

    private function parseVariable(): Branch
    {
        $lexer    = $this->lexer;
        $varName  = $lexer->token->value;
        $children = [];
        $meta     = [];
        $this->assertNotReservedWord($varName);

        if ($lexer->lookahead && $lexer->lookahead->type === TemplateLexer::T_OPEN_PAREN) {
            throw new ParserException('Calling a function directly is not currently supported');
        }

        while ($lexer->lookahead && $lexer->lookahead->type == TemplateLexer::T_PERIOD) {
            $lexer->moveNext();
            if (!$lexer->lookahead || $lexer->lookahead->type != TemplateLexer::T_STRING) throw new ParserException('Expecting ' . TemplateLexer::T_STRING . ' after ' . TemplateLexer::T_PERIOD);
            $lexer->moveNext();
            $args     = null;
            $propName = $lexer->token->value;

            if ($lexer->lookahead->type == TemplateLexer::T_OPEN_PAREN) {
                $lexer->moveNext();
                $args = $this->parseFunctionParenthesis();
            }
            if (!isset($branch['children'])) $branch['children'] = [];

            if ($args === null) {
                $children[] = new Branch(self::T_PROPERTY_OR_FUNC, $propName);
            } else {
                $children[] = new Branch(self::T_FUNCTION, $propName, [], $args);
            }
        }

        // Consume whitespace
        while ($lexer->isNextToken(TemplateLexer::T_WHITESPACE)) {
            $lexer->moveNext();
        }

        // Check for default value for variable, e.g. variable ?? defaultVar or variable ?? "default value"
        if ($lexer->isNextToken(TemplateLexer::T_QUESTION)) {
            $peek = $lexer->peek();
            if ($peek === null || $peek->type != TemplateLexer::T_QUESTION) throw new ParserException('Unexpected ' . TemplateLexer::T_QUESTION . ' after variable ' . $varName);
            $lexer->moveNext();
            $lexer->moveNext();
            while ($lexer->isNextToken(TemplateLexer::T_WHITESPACE)) {
                $lexer->moveNext();
            }

            if (!$lexer->lookahead) throw new ParserException('Unexpected end of input when evaluating default expression after ' . $varName);
            $lexer->moveNext();

            if (in_array($lexer->token->type, [TemplateLexer::T_SINGLE_QUOTE, TemplateLexer::T_DOUBLE_QUOTE])) {
                $default = $this->parseString($lexer->token->type);
            } else {
                $default = $this->parseVariable();
            }
            $meta['default'] = $default;
        }

        return new Branch(self::T_VARIABLE, $varName, $children, [], $meta);
    }

    /**
     * @return Branch[]
     */
    public function parseFunctionParenthesis(): array
    {
        $lexer = $this->lexer;
        if (!$lexer->lookahead->isA(TemplateLexer::T_OPEN_PAREN)) throw new ParserException('Expecting ' . TemplateLexer::T_OPEN_PAREN);
        $lexer->moveNext();

        $branches     = [];
        $parameter    = null;
        $previousType = null;

        while ($lexer->lookahead) {
            $lexer->moveNext();

            switch ($lexer->token->type) {
                case TemplateLexer::T_CLOSE_PAREN:
                    if ($parameter !== null) $branches[] = $parameter;
                    break 2;
                case TemplateLexer::T_COMMA:
                    if ($parameter === null) {
                        throw new ParserException('Cannot have empty parameter followed by ' . TemplateLexer::T_COMMA);
                    }
                    $branches[]   = $parameter;
                    $parameter    = null;
                    $previousType = null;
                    continue 2;
                case TemplateLexer::T_SINGLE_QUOTE:
                case TemplateLexer::T_DOUBLE_QUOTE:
                    if ($parameter !== null && $previousType != TemplateLexer::T_PLUS) throw new ParserException('Unexpected ' . $lexer->token->type . '. Missing ' . TemplateLexer::T_PLUS . ' separator in code');

                    $parameter = $this->createOrUpdateFunctionArgument(
                        $this->parseString($lexer->token->type),
                        $parameter
                    );
                    break;
                case TemplateLexer::T_STRING:
                    if ($parameter !== null && $previousType != TemplateLexer::T_PLUS) throw new ParserException('Unexpected ' . $lexer->token->type . '. Missing ' . TemplateLexer::T_PLUS . ' separator in code');
                    $parameter = $this->createOrUpdateFunctionArgument($this->parseVariable(), $parameter);
                    break;
                case TemplateLexer::T_PLUS:
                    if ($previousType == TemplateLexer::T_PLUS) throw new ParserException('Unexpected ' . TemplateLexer::T_PLUS);
                    break;
                case TemplateLexer::T_OPEN_BRACKET:
                    $parameter = $this->parseObject();
                    break;
                case TemplateLexer::T_WHITESPACE:
                case TemplateLexer::T_NEWLINE:
                    continue 2;
                default:
                    echo 'Unknown: ' . $this->tokenDebug();
                    die(__FILE__ . ':' . __LINE__ . '<br />' . PHP_EOL);
            }

            $previousType = $lexer->token->type;
        }

        if ($lexer->token->type != TemplateLexer::T_CLOSE_PAREN) throw new ParserException('Expecting ' . TemplateLexer::T_CLOSE_PAREN . ', but reached end of string');

        return $branches;
    }

    public function parseObject(): Branch
    {
        if (!$this->lexer->token->isA(TemplateLexer::T_OPEN_BRACKET)) throw new ParserException('Expecting ' . TemplateLexer::T_OPEN_BRACKET . ', but found '. $this->lexer->token->type);
        while ($this->lexer->isNextToken(TemplateLexer::T_WHITESPACE)) {
            $this->lexer->moveNext();
        }
        #if (!$this->lexer->isNextTokenAny([TemplateLexer::T_SINGLE_QUOTE, TemplateLexer::T_DOUBLE_QUOTE])) throw
        $keyName = null;
		$keyMeta = [];
        $children = [];
        $prevToken = null;
        while ($this->lexer->lookahead) {
            $this->lexer->moveNext();

            switch($this->lexer->token->type) {
                case TemplateLexer::T_CLOSE_BRACKET:
                    if ($keyName !== null) throw new ParserException('Did not find matching value for ' . $keyName . ' when parsing object');
                    return new Branch(self::T_OBJECT, null, $children);
                case TemplateLexer::T_SINGLE_QUOTE:
                case TemplateLexer::T_DOUBLE_QUOTE:
                    $str = $this->parseString($this->lexer->token->type);
                    if ($keyName === null) {
                        $keyName = $str->getValue();
						$keyMeta = $str->getMeta();
                    } else {
                        if ($prevToken != TemplateLexer::T_COLON) throw new ParserException('Looking for ' . TemplateLexer::T_COLON . ', but found ' . $this->lexer->token->type . ' when parsing object');
                        $children[] = new Branch(self::T_KEY_VALUE_PAIR, $keyName, [$str], [], $keyMeta->toArray());
                        $keyName = null;
                    }
                    break;
                case TemplateLexer::T_WHITESPACE:
                case TemplateLexer::T_NEWLINE:
                    continue 2;
                case TemplateLexer::T_COMMA:
                    if ($keyName !== null) throw new ParserException(TemplateLexer::T_COMMA . ' is optional, but can only be used after a value in a key-value pair');
                    break;
                case TemplateLexer::T_STRING:
                    if ($keyName === null) throw new ParserException('Cannot use variable as key for an object');
                    else if ($prevToken != TemplateLexer::T_COLON) throw new ParseException('looking for ' . TemplateLexer::T_COLON . ', but found ' . $this->lexer->token->type);
                    $variable = $this->parseVariable();
                    $children[] = new Branch(self::T_KEY_VALUE_PAIR, $keyName, [$variable], [], $keyMeta->toArray());
                    $keyName = null;
                    break;
                case TemplateLexer::T_COLON:
                    break;
                default:
                    throw new ParserException('Unexpected token ' . $this->lexer->token->type . ' while parsing object');
            }
            $prevToken = $this->lexer->token->type;
        }

        throw new ParserException('Reached end of input without finding ' . TemplateLexer::T_CLOSE_BRACKET);
    }

    private function createOrUpdateFunctionArgument(Branch $currentArgument, ?Branch $currentBranch): Branch
    {
        if ($currentBranch === null) {
            return $currentArgument;
        } else {
            $children   = $currentBranch->getType() == self::T_CODE ? $currentBranch->getChildren() : [$currentBranch];
            $children[] = $currentArgument;

            return new Branch(self::T_CODE, null, $children);
        }
    }

    public function parseString(string $quoteType): Branch
    {
        $allowedQuoteTypes = [TemplateLexer::T_SINGLE_QUOTE, TemplateLexer::T_DOUBLE_QUOTE];
        if (!in_array($quoteType, $allowedQuoteTypes)) throw new \InvalidArgumentException('Only ' . implode(', ', $allowedQuoteTypes) . ' types are supported in ' . __METHOD__);
        $lexer = $this->lexer;
        if ($lexer->token->type != $quoteType) throw new ParserException('Expecting ' . $quoteType);

        $lexer->moveNext();
        $string = '';
        while (true) {
            if (!$lexer->lookahead) throw new ParserException('Expecting ' . $quoteType . ', but found end of string');
            if ($lexer->token->type == $quoteType) break;

            $string .= $lexer->token->value;
            $lexer->moveNext();
        }

        return new Branch(self::T_STRING, $string, [], [], [self::META_QUOTE_TYPE => $quoteType]);
    }

    private function parseComment(): ?Branch
    {
        if (!$this->lexer->token->isA(TemplateLexer::T_FORWARD_SLASH) || !$this->lexer->isNextTokenAny([TemplateLexer::T_FORWARD_SLASH, TemplateLexer::T_ASTERISK])) return null;
        $isBlockComment = $this->lexer->lookahead->isA(TemplateLexer::T_ASTERISK);
        $endTokens      = $isBlockComment ? [TemplateLexer::T_ASTERISK, TemplateLexer::T_FORWARD_SLASH] : [TemplateLexer::T_NEWLINE];
        $this->lexer->moveNext();

        $comment = '';

        // Parse until end of comment, NEWLINE for // and */ for /*
        while ($this->lexer->lookahead) {
            $this->lexer->moveNext();
            if (TokenHelper::checkPattern($this->lexer, $endTokens)) {
                // Advance token cursor
                for ($i = 0; $i < count($endTokens) - 1; $i++) {
                    $this->lexer->moveNext();
                }
                break;
            }
            $comment .= $this->lexer->token->value;
        }

        // Try to clear out extra whitespace
        while ($this->lexer->isNextTokenAny([TemplateLexer::T_WHITESPACE, TemplateLexer::T_NEWLINE])) {
            $this->lexer->moveNext();
        }

        return new Branch($isBlockComment ? self::T_BLOCK_COMMENT : self::T_INLINE_COMMENT, $comment);
    }

    private function tokenDebug($token = null): string
    {
        return TemplateLexerDebugger::tokenDebug($this->lexer, $token);
    }

    private function assertNotReservedWord(string $word)
    {
        static $reserved;
        $php      = ['abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'readonly', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor', 'yield'];
        $js       = ['abstract', 'arguments', 'await', 'boolean', 'break', 'byte', 'case', 'catch', 'char', 'class', 'const', 'continue', 'debugger', 'default', 'delete', 'do', 'double', 'else', 'enum', 'eval', 'export', 'extends', 'false', 'final', 'finally', 'float', 'for', 'funciton', 'goto', 'if', 'implements', 'import', 'in', 'instanceof', 'int', 'interface', 'let', 'long', 'native', 'new', 'null', 'package', 'private', 'protected', 'public', 'return', 'short', 'static', 'super', 'switch', 'synchronized', 'this', 'throw', 'throws', 'transient', 'true', 'try', 'typeof', 'var', 'void', 'volatile', 'while', 'with', 'yield'];
        $html     = ['window', 'console'];
        $react    = ['react'];
        $template = ['call', 'debug', 'for', 'endfor', 'foreach', 'endforeach', 'function', 'if', 'elseif', 'else', 'include', 'section'];
        if ($reserved === null) {
            $reserved = array_unique(array_merge($php, $js, $html, $react, $template));
        }

        if (in_array($word, $reserved)) throw new ParserException('"' . $word . '" is a reserved name');
    }

    public function __invoke(string $filename)
    {
        return $this->parse(file_get_contents($filename));
    }
}
