<?php

namespace WebImage\BlockManager\src\Templates\Transpilers;

use WebImage\BlockManager\src\Templates\Parsers\Branch;

class TranspilerDebug
{
    public static function dumpBranch(?Branch $branch, $depth=0): string
    {
        if ($branch === null) return 'NULL';

        $value = $branch->getType();
        $value .= '(';
        $value .= count($branch->getArgs()) . ' args';
        $value .= ')';
        $value .= PHP_EOL;

        self::dumpChildren($branch->getChildren());

        return $value;
    }

    private static function dumpChildren(array $branches): string
    {
        if (count($branches) === 0) return '';

        $value = '<ul>';
        foreach($branches as $branch) {
            $value .= '<li>' . self::dumpBranch($branch) . '</li>' . PHP_EOL;
        }
        $value .= '</ul>';

        return $value;
    }
}
