<?php

namespace WebImage\Blocks\Templates\Parsers;

use WebImage\Blocks\Templates\ImmutableMeta;

final class Branch
{
    private string  $type;
    private ?string $value;
    private array   $args;
	private ImmutableMeta    $meta;
    private array   $children;

    /**
     * @param string $type
     * @param string|null $value
     * @param array $children
     * @param array $args
     */
    public function __construct(string $type, ?string $value = null, array $children = [], array $args = [], array $meta = [])
    {
        $this->type  = $type;
        $this->value = $value;
        $this->setChildren($children);
        $this->setArgs($args);
        $this->meta = new ImmutableMeta($meta);
    }

    private function setChildren(array $children): void
    {
        // Assert children are of type Branch
        foreach ($children as $ix => $child) {
            $debugType = $this->getDebugType($child);
            if (!($child instanceof Branch)) {
                throw new \RuntimeException('Child ' . $ix . ' must be an instance of Branch for ' . $this->type . ' instead of ' . $debugType);
            }
        }

        $this->children = $children;
    }

    private function setArgs(array $args): void
    {
        foreach ($args as $ix => $arg) {
            $debugType = $this->getDebugType($arg);
            if (!($arg instanceof Branch)) throw new \RuntimeException('Argument ' . $ix . ' must be an instance of Branch for ' . $this->type . ' instead of ' . $debugType);
        }

        $this->args = $args;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return Branch[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return array | Branch[]
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @return ImmutableMeta
     */
    public function getMeta(): ImmutableMeta
    {
        return $this->meta;
    }

    public function toArray(): array
    {
        return [
            'type' => self::getType(),
            'value' => self::getValue(),
            'children' => array_map(function(Branch $branch) {
                return $branch->toArray();
            }, $this->getChildren()),
            'arguments' => array_map(function(Branch $branch) {
                return $branch->toArray();
            }, $this->getArgs()),
            'meta' => $this->meta->toArray()
        ];
    }

    public static function fromArray(array $branch): Branch
    {
        $type = $branch['type'] ?? '';
        $value = $branch['value'];
        $children = isset($branch['children']) ? array_map(function(array $branch) {
            return Branch::fromArray($branch);
        }, $branch['children']) : [];
        $args = isset($branch['args']) ? array_map(function(array $branch) {
            return Branch::fromArray($branch);
        }, $branch['args']) : [];
        $meta = isset($branch['meta']) ? $branch['meta'] : [];

        return new Branch($type, $value, $children, $args, $meta);
    }

    /**
     * Get friendly description for an unexpected type... thanks to PHP for not type-checking arrays, ugh..
     * @param $val
     * @return string
     */
    private function getDebugType($val): string
    {
        $type = gettype($val);
        if (is_object($type)) return get_class($type);

        return $type;
    }
}
