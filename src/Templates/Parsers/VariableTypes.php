<?php

namespace WebImage\Blocks\Templates\Parsers;

use WebImage\Blocks\Processing\UnsupportedVariableTypeException;

class VariableTypes
{
	const TYPE_INT = 'int';
	const TYPE_STRING = 'string';
	const TYPE_FLOAT = 'float';
	const TYPE_OBJECT = 'object';

	// Asserts that the type specified is valid and returns it
	public static function validType(?string $type): string
	{
		if ($type === null) throw new UnsupportedVariableTypeException('A valid type must be specified');
		else if (!in_array($type, self::getValidTypes())) throw new UnsupportedVariableTypeException('Invalid type specified: ' . $type . '.  Expecting: ' . implode(', ', self::getValidTypes()));
		return $type;
	}

	public static function getValidTypes(): array
	{
		return [self::TYPE_INT, self::TYPE_FLOAT, self::TYPE_STRING, self::TYPE_OBJECT];
	}
}