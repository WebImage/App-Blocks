<?php

declare(strict_types=1);

namespace WebImage\BlockManager\Templates\Lexers;

use function in_array;

/**
 * @template T of string|int
 * @template V of string|int
 */
final class Token
{
    /**
     * The string value of the token in the input string
     *
     * @readonly
     * @var V
     */
    public $value;

    /**
     * The type of the token (identifier, numeric, string, input parameter, none)
     *
     * @readonly
     * @var T|null
     */
    public $type;

    /**
     * The position of the token in the input string
     *
     * @readonly
     * @var int
     */
    public int $position;

    /**
     * @param V      $value
     * @param T|null $type
     */
    public function __construct($value, $type, int $position)
    {
        $this->value    = $value;
        $this->type     = $type;
        $this->position = $position;
    }

    /** @param T ...$types */
    public function isA(...$types): bool
    {
        return in_array($this->type, $types, true);
    }
}
