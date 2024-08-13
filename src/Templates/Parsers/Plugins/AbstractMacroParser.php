<?php

namespace WebImage\Blocks\Templates\Parsers\Plugins;

use WebImage\Blocks\Templates\Context;
use WebImage\Blocks\Templates\Lexers\Token;
use WebImage\Blocks\Templates\Meta;
use WebImage\Blocks\Templates\Parsers\AbstractParserPlugin;
use WebImage\Blocks\Templates\Parsers\Branch;
use WebImage\Blocks\Templates\Parsers\BranchArgumentDefinition;
use WebImage\Blocks\Templates\Parsers\ParserDebugger;
use WebImage\Blocks\Templates\Parsers\ParserException;
use WebImage\Blocks\Templates\Parsers\ParserState;
use WebImage\Blocks\Templates\Parsers\TemplateParser;

abstract class AbstractMacroParser extends AbstractParserPlugin
{
	const CONTEXT_MACRO_NAME = 'macro.name';
	const CONTEXT_MACRO_ARGS = 'macro.args';
	protected array $supportedMacros = [];

	protected ?ParserState $currentState = null;

	public function canParseText(ParserState $state): bool
	{
		$lexer = $state->lexer;

		if (!$lexer->token->isA($lexer::T_AT)) return false;
		else if (!$lexer->lookahead->isA($lexer::T_STRING)) return false;

		$macroName = $lexer->lookahead->value;

		return in_array($macroName, $this->supportedMacros);
	}

	public function parseText(ParserState $state, array $untilBranchTypes = null): ?Branch
	{
		static $depth = 0;

		return ParserDebugger::wrapBranch(function () use ($state, $untilBranchTypes, &$depth) {
			$restoreState       = $this->currentState;
			$this->currentState = $state;
			$name               = $this->parseName();
			$args               = $this->parseArguments($name);

			$context = $this->_startContext($name, $args);

			$this->_assertContext();
			$this->processArguments($state, $args);
			$this->removeTrailingWhitespace();

			$branch = $this->createBranch($state, $this->getBranchType($name), $name, $this->processBody($state, $name), $args, $this->getMeta());

			$this->_endContext($name, $context);
			$this->currentState = $restoreState;

			if ($branch === null) {
				ParserDebugger::echo('createBranch() returned NULL for ' . $name . '/' . $this->getBranchType($name), $state);
			} else {
				ParserDebugger::echo('Creating branch', $state);
			}

			return $branch;
		}, $state);
	}

	protected function createContext(): array
	{
		return [];
	}

	private function _startContext(string $name, array $args): Meta
	{
		return $this->currentState->context->push(array_merge(
															  [
																  self::CONTEXT_MACRO_NAME => $name,
																  self::CONTEXT_MACRO_ARGS => $args
															  ],
															  $this->createContext()
														  ));
	}

	/**
	 * Removes the started context
	 * @param Meta $context
	 * @return void
	 */
	private function endContext(Meta $context)
	{
		$this->currentState->context->remove($context);
	}

	private function _endContext(string $name, Meta $context)
	{
		if (!$this->currentState->context->isSetInCurrentContext(self::CONTEXT_MACRO_NAME)) {
			throw new ParserException('Another context was started within @' . $name . ' that was not terminated');
		}
		$this->endContext($context);
	}

	/**
	 * Ensure that all required meta values are set.  For example, if a control must exist within a parent control that sets meta values... then check that here.
	 * @param Context $context
	 * @return void
	 * @throws ParserException
	 */
	protected function assertContext(): void
	{
	}

	private function _assertContext(): void
	{
		if (!isset($this->currentState->context[self::CONTEXT_MACRO_NAME])) throw new ParserException('Context is missing ' . self::CONTEXT_MACRO_NAME . '. Was $context->pop() called somewhere within ' . get_class($this) . ' or another macro within ' . $this->getMacroName());
		$this->assertContext();
	}

	protected function createBranch(ParserState $state, string $type, string $name, array $children = [], array $args = [], array $meta = []): ?Branch
	{
		return new Branch(
			$type,
			$name,
			$children,
			$args,
			$meta
		);
	}

	protected function getBranchType(string $macroName): string
	{
		return TemplateParser::T_MACRO;
	}

	/**
	 * @return array A key value array to initialize Meta()
	 */
	protected function getMeta(): array
	{
		return [];
	}

	/**
	 * If this macro has arguments we can specify name them by position here.  Add a "?" to the end of any optional requirements
	 * @param string $macroName
	 * @return BranchArgumentDefinition[]
	 */
	public static function getArgumentDefinitions(string $macroName): array
	{
		return [];
	}

	private function parseName(): string
	{
		$lexer = $this->currentState->lexer;
		$this->assertType($lexer::T_AT);
		$this->assertType($lexer::T_STRING, $lexer->lookahead);
		$lexer->moveNext();

		$name = $lexer->token->value;
//        $lexer->moveNext();

		return $name;
	}

	/**
	 * @param string $macroName
	 * @return BranchArgumentDefinition[]
	 */
	private function parseArguments(string $macroName): array
	{
		$args     = [];
		$lexer    = $this->currentState->lexer;
		$parser   = $this->currentState->parser;
		$required = self::getRequiredArgumentNames($macroName);

		$minArgs = count($required);
		$maxArgs = self::getMaxArguments($macroName);

		if ($this->currentState->lexer->lookahead !== null && $this->currentState->lexer->lookahead->isA($lexer::T_OPEN_PAREN)) {
			$this->assertValidArgumentDefinitions($macroName);

			$args = $parser->parseFunctionParenthesis();

			$nArgs = count($args);

			$argNames = array_map(function (BranchArgumentDefinition $arg) {
				return $arg->getName();
			}, static::getArgumentDefinitions($macroName));

			if ($nArgs < $minArgs) throw new ParserException('@' . $macroName . ' requires at least ' . $minArgs . ' (' . implode(', ', $required) . ')');
			else if ($maxArgs >= 0 && $nArgs > $maxArgs) throw new ParserException('@' . $macroName . ' only has ' . $minArgs . ' arguments (' . implode(', ', $argNames) . '), but ' . $nArgs . ' were specified');
		} else if ($minArgs > 0) {
			throw new ParserException('@' . $macroName . ' requires at least ' . $minArgs . ' (' . implode(', ', $required) . ')');
		}

		return $args;
	}

	/**
	 * Gets the max number of arguments this macro can accept, or -1 to indicate unlimited
	 * @return int
	 */
	public static function getMaxArguments(string $macroName): int
	{
		$nArgs = count(static::getArgumentDefinitions($macroName));

		foreach (static::getArgumentDefinitions($macroName) as $def) {
			if ($def->hasMultiple()) return -1;
		}

		return $nArgs;
	}

	private function assertValidArgumentDefinitions(string $macroName): void
	{
		$anyNotRequired = null;
		$multiArgument  = null;
		foreach (static::getArgumentDefinitions($macroName) as $def) {
			// Check that "required" is set up correctly
			if ($anyNotRequired !== null && $def->isRequired()) throw new ParserException(sprintf('@%s macro cannot specify an argument (%s) as required after a not-required argument (%s)', $macroName, $def->getName(), $anyNotRequired->getName()));
			if (!$def->isRequired()) $anyNotRequired = $def;
			// Check that "mutliple" is set up correctly
			if ($multiArgument !== null) throw new ParserException(sprintf('@%s macro must only have one argument that accepts multiple arguments, and it must be last. %s was set to have multiple arguments after %s was already set with multiple.', $macroName, $def->getName(), $multiArgument->getName()));
			if ($def->hasMultiple()) $multiArgument = $def;
		}
	}


	public function getMacroName(): string
	{
		return $this->currentState->context[self::CONTEXT_MACRO_NAME];
	}

	/**
	 * Allows inheriting class to do something with the passed arguments
	 * @param Branch[]|null $args
	 * @return void
	 */
	protected function processArguments(ParserState $state, array $args)
	{
	}

	/**
	 * @return Branch[]
	 */
	protected function processBody(ParserState $state, string $macroName): array
	{
		return [];
	}

	/**
	 *
	 * @return array
	 */
	public static function getRequiredArgumentNames($macroName): array
	{
		$argNames = [];
		foreach (static::getArgumentDefinitions($macroName) as $argument) {
			if ($argument->isRequired()) $argNames[] = $argument->getName();
		}
		return $argNames;
	}

	/**
	 * @param Branch[] $args [key => Branch[]]
	 * @param string $name
	 * @return string
	 */
	public static function getArgumentValueByName(string $macroName, array $args, string $name): ?Branch
	{
		$argIx = static::getArgumentIndexForName($macroName, $name);

		if ($argIx < 0) throw new ParserException(sprintf('Unable to find parameter named %s for macro @%s', $name, $macroName));
		if ($argIx > count($args) - 1) return null;

		$argDef = static::getArgumentDefinitions($macroName)[$argIx];

		if ($argDef->hasMultiple()) throw new ParserException('@' . $macroName . ' argument ' . ($argIx + 1) . ' (' . $name . ') is allowed to have multiple values and must be retrieved with getArgumentStringsByName(...)');

		return $args[$argIx];
	}

	/**
	 * @param Branch[] $args [key => Branch[]]
	 * @param string $name
	 * @return string
	 */
	public static function getArgumentStringByName(string $macroName, array $args, string $name): ?string
	{
		$argIx = static::getArgumentIndexForName($macroName, $name);

		if ($argIx < 0) throw new ParserException(sprintf('Unable to find parameter named %s for macro @%s', $name, $macroName));
		if ($argIx > count($args) - 1) return null;

		$argDef = static::getArgumentDefinitions($macroName)[$argIx];

		if ($argDef->hasMultiple()) throw new ParserException('@' . $macroName . ' argument ' . ($argIx + 1) . ' (' . $name . ') is allowed to have multiple values and must be retrieved with getArgumentStringsByName(...)');
		else if ($args[$argIx]->getType() !== TemplateParser::T_STRING) throw new ParserException('@' . $macroName . ' argument ' . ($argIx + 1) . ' (' . $name . ') must be a simple string type');

		return $args[$argIx]->getValue();
	}

	public static function getArgumentStringsByName(string $macroName, array $args, string $name): ?string
	{
		$argIx = static::getArgumentIndexForName($macroName, $name);

		if ($argIx < 0) throw new ParserException(sprintf('Unable to find parameter named %s for macro @%s', $name, $macroName));
		if ($argIx > count($args) - 1) return null;

		$argDef = static::getArgumentDefinitions($macroName)[$argIx];

		if (!$argDef->hasMultiple()) throw new ParserException('@' . $macroName . ' argument ' . ($argIx + 1) . ' (' . $name . ') is not allowed to have multiple values and must be retrieved with getArgumentStringByName(...)');

		die(__FILE__ . ':' . __LINE__ . '<br />' . PHP_EOL);
		#else if ($args[$argIx]->getType() !== TemplateParser::T_STRING) throw new ParserException('@' . $macroName . ' argument ' . ($argIx + 1) . ' (' . $name . ') must be a simple string type');

		return $args[$argIx]->getValue();
	}

	public static function getArgumentIndexForName(string $macroName, string $name): int
	{
		foreach (static::getArgumentDefinitions($macroName) as $ix => $def) {
			if ($def->getName() == $name) return $ix;
		}

		return -1;
	}

	private function removeTrailingWhitespace()
	{
		$lexer = $this->currentState->lexer;
		while ($lexer->isNextToken($lexer::T_WHITESPACE) || $lexer->isNextToken($lexer::T_NEWLINE)) {
			$lexer->moveNext();
		}
	}

	/**
	 * Ensure that the current token is a specific type
	 * @param string $assertType
	 * @param array|null $current
	 * @return void
	 */
	private function assertType(string $assertType, Token $current = null)
	{
		if ($current === null) $current = $this->currentState->lexer->token;

		if ($current->type != $assertType) throw new ParserException('Expecting ' . $assertType . ' but found ' . $current->type);
	}
}
