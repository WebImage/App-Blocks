<?php

namespace WebImage\BlockManager\src\Templates\Parsers;

use WebImage\BlockManager\src\Templates\Context;
use WebImage\BlockManager\src\Templates\Lexers\TemplateLexer;
use WebImage\BlockManager\src\Templates\Meta;

class ParserState
{
    const CAPTURE_GROUP = '_CAPTURE';
    const CONTEXT_DELEGATE = 'parser.delegate';

    public TemplateParser $parser;
    public TemplateLexer  $lexer;
    public Meta           $meta;
    public Context        $context;

    /**
     * @param \WebImage\BlockManager\src\Templates\Parsers\TemplateParser $parser
     * @param TemplateLexer $lexer
     * @param Meta $meta
     * @param Context $context
     */
    public function __construct(TemplateParser $parser, TemplateLexer $lexer, Meta $meta, Context $context)
    {
        $this->parser  = $parser;
        $this->lexer   = $lexer;
        $this->meta    = $meta;
        $this->context = $context;
    }

    public function delegateParsing(ParserPluginInterface $delegatePlugin, callable $callback, array $context=null): ?Branch
    {
        ParserDebugger::echo('ParserState.delegateParsing: delegatePlugin: ' . get_class($delegatePlugin));

        $context = array_merge([self::CONTEXT_DELEGATE => $delegatePlugin], $context ?? []);
        $ref = $this->context->push($context);
        $branch = call_user_func($callback, $this);

        if (!isset($this->context[self::CONTEXT_DELEGATE])) {
            throw new ParserException('Attempting to pop context for DELEGATE, but something else has modified the stack and did not return it to its normal state');
        }
        $this->context->remove($ref);//$this->context->pop();

        return $branch;
    }

    public function getDelegateParser(): ?ParserPluginInterface
    {
        return $this->context[self::CONTEXT_DELEGATE];
    }
}
