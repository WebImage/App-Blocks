<?php

namespace WebImage\Blocks\Processing;

use WebImage\Blocks\Templates\Parsers\VariableTypes;

class ReactVariableTypes
{
	public static function getReactType(string $type): string
	{
		switch($type) {
			case VariableTypes::TYPE_FLOAT:
			case VariableTypes::TYPE_INT:
				return 'number';
				break;
			case VariableTypes::TYPE_STRING:
				return 'string';
				break;
			default:
				throw new UnsupportedVariableTypeException('The variable type ' . $type . ' does not have a React equivalent defined');
		}
	}
}