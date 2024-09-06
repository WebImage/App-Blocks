<?php

namespace WebImage\Blocks;

use WebImage\Blocks\Services\BlockManager;
use WebImage\Blocks\Services\InvalidBlockException;
use WebImage\Core\ArrayHelper;

class BlockType
{
	private string $type;
	private string $label;
	private array  $allowedChildren = [];
	private array  $allowedParents  = [];

	/**
	 * @param string $type
	 * @param string $label
	 * @param array $allowedChildren
	 * @param array $allowedParents
	 */
	public function __construct(string $type, string $label, array $allowedChildren = [], array $allowedParents = [])
	{
		$this->type            = $type;
		$this->label           = $label;
		$this->allowedChildren = $allowedChildren;
		$this->allowedParents  = $allowedParents;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function getAllowedChildren(): array
	{
		return $this->allowedChildren;
	}

	public function getAllowedParents(): array
	{
		return $this->allowedParents;
	}

	public function import(BlockManager $blockManager, array $block): Block
	{
		return new Block(
			$this->getType(),
			$this->importChildren($blockManager, $block),
			$block['id'] ?? null,
			$block['config'] ?? [],
			$block['data'] ?? []
		);
	}

	private function importChildren(BlockManager $blockManager, array $block): array
	{
		$children = [];
		if (array_key_exists('block', $block) && array_key_exists('children', $block)) {
			throw new InvalidBlockException('Block cannot have both "block" and "children" keys');
		}
		$blockChildren = [];
		if (array_key_exists('block', $block)) {
			if (!ArrayHelper::isAssociative($block['block'])) {
				throw new InvalidBlockException('Child block must be an associative array');
			}
			$blockChildren = [$block['block']];
		} elseif (array_key_exists('children', $block)) {
			$blockChildren = $block['children'];
		}

		foreach ($blockChildren as $child) {
			$children[] = $blockManager->import($child);
		}

		return $children;
	}

	public function export(BlockManager $blockManager, Block $block): array
	{
		$data         = [];
		$data['type'] = $block->getType();

		if ($block->getId() !== null) $data['id'] = $block->getId();

		$children = [];

		foreach ($block->getChildren() as $child) {
			$children[] = $blockManager->export($child);
		}

		if (count($children) > 0) $data['children'] = $children;
		if (count($block->getConfig()) > 0) $data['config'] = $block->getConfig();
		if (count($block->getData()) > 0) $data['data'] = $block->getData();

		return $data;
	}

	public function validate(BlockManager $blockManager, Block $block): void
	{
	}
}