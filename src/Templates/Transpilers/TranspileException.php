<?php

namespace WebImage\BlockManager\Templates\Transpilers;

class TranspileException extends \Exception
{
    public static function forEmbeddedStateInstantiation(string $method, string $variableName): TranspileException
    {
        return new TranspileException(sprintf('Cannot call %s with non-null $%s value from within another %1$s call', $method, $variableName));
    }
}
