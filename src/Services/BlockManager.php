<?php

namespace WebImage\Blocks\Services;

use WebImage\Blocks\Block;
use WebImage\Blocks\BlockNode;
use WebImage\Blocks\BlockType;
use WebImage\Core\Dictionary;

class BlockManager
{
	private Dictionary $types;
	const BLOCK_TYPE_ROOT = 'root';
	const BLOCK_TYPE_UNKNOWN = 'unknown';

	public function __construct()
	{
		$this->types = new Dictionary();
	}

	public function registerType(BlockType $type): void
	{
		$this->assertValidType($type);
		$this->types->set($type->getType(), $type);
	}

//	public function createBlock(string $type = null): Block
//	{
//		if ($type === null) {
//			return new Block($this, 'root');
//		}
//
//		if (!$this->types->has($type)) {
//			throw new \InvalidArgumentException(sprintf('Block type "%s" does not exist', $type));
//		}
//		return new Block($this, $type);
//	}

	public function getType(string $type): BlockType
	{
		if ($type == self::BLOCK_TYPE_ROOT) {
			return new BlockType(self::BLOCK_TYPE_ROOT, 'Root');
		}

		if (!$this->hasType($type)) {
			throw new \InvalidArgumentException(sprintf('Block type "%s" does not exist', $type));
		}

		return $this->types->get($type);
	}

	public function hasType(string $type): bool
	{
		return $type == self::BLOCK_TYPE_ROOT || $this->types->has($type);
	}

	public function import(array $data): Block
	{
		$typeName = $data['type'] ?? self::BLOCK_TYPE_ROOT;
		if (!$this->hasType($typeName)) return $this->importUnknownBlockData($data); // Support importing unknown types to allow for later validation
		$type = $this->getType($typeName);

		return $type->import($this, $data);
	}

	private function importUnknownBlockData(array $data): Block
	{
		$typeName = $data['type'] ?? self::BLOCK_TYPE_UNKNOWN;

		return new Block(
			$typeName,
			array_map(
				function(array $childData) {
					return $this->import($childData);
				}, $data['children'] ?? []
			),
			$data['id'] ?? null
		);
	}

	public function export(Block $block): array
	{
		$type = $this->getType($block->getType());

		return $type->export($this, $block);
	}

	public function validate(Block $block): void
	{
		$this->validateBlock($block);
	}

	private function validateBlock(Block $block, string $debugPath = 'root', array $parents = [], int $depth = 1): void
	{
		if (!$this->hasType($block->getType())) {
			throw new InvalidBlockException(sprintf('Unknown block type "%s" at %s', $block->getType(), $debugPath));
		}

		$type = $this->getType($block->getType());

		try {
			$type->validate($this, $block);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidBlockException($e->getMessage() . ' at ' . $debugPath, 0, $e);
		}

		$this->assertValidParents($block, $debugPath, $parents, $depth);
		$this->assertValidChild($block, $debugPath, $parents, $depth);

		$childParents = array_merge($parents, [$block]);

		foreach ($block->getChildren() as $ix => $child) {
			$childDebugPath = $debugPath . '[' . $ix . ':' . $child->getType() . ']';
			$this->validateBlock($child, $childDebugPath, $childParents, $depth + 1);
		}
	}

	/**
	 * @param Block $block
	 * @param string $debugPath
	 * @param Block[] $parents
	 * @param int $depth
	 * @return void
	 */
	private function assertValidChild(Block $block, string $debugPath, array $parents, int $depth = 1): void//(BlockType $type, Block $child, string $debugPath): void
	{
		if (count($parents) == 0) return;

		$parent     = $parents[count($parents) - 1];
		$parentType = $this->getType($parent->getType());

		if (count($parentType->getAllowedChildren()) == 0) return;

		if (!in_array($block->getType(), $parentType->getAllowedChildren())) {
			throw new \InvalidArgumentException(sprintf('Block type "%s" is not allowed as a child of "%s" at %s', $block->getType(), $parentType->getType(), $debugPath));
		}
	}

	private function assertValidParents(Block $block, string $debugPath, array $parents, int $depth = 1): void
	{

		$type         = $this->getType($block->getType());
		$validParents = $type->getAllowedParents();

		if (count($validParents) == 0) return;
		if (count($parents) == 0) throw new InvalidBlockException(sprintf('Block type "%s" is not allowed as a root block', $type->getType()));

		$parentType = $parents[count($parents) - 1]->getType();

		if (!in_array($parentType, $validParents)) {
			throw new InvalidBlockException(sprintf('Block type "%s" is not allowed as a child of "%s" at %s', $type->getType(), $parentType, $debugPath));
		}
	}

	private function assertValidType(BlockType $type): void
	{
		if ($this->isReservedType($type->getType())) {
			throw new \InvalidArgumentException(sprintf('Block type "%s" is reserved', $type->getType()));
		}
		if ($this->types->has($type->getType())) {
			throw new \InvalidArgumentException(sprintf('Block type "%s" already exists', $type->getType()));
		}
	}

	private function isReservedType(string $type): bool
	{
		return $type == 'root';
	}
}