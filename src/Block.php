<?php

namespace WebImage\Blocks;

use WebImage\Blocks\Services\BlockManager;
use WebImage\Core\ArrayHelper;
use WebImage\Core\ImmutableDictionary;

class Block
{
	private string  $type;
	private array   $children = [];
	private ?string $id;
	private ?Block  $parent   = null;
	private array   $config   = [];
	private array   $data     = [];

	/**
	 * @param string $type
	 * @param array $children
	 * @param string|null $id
	 * @param array $config
	 * @param array $data
	 */
	public function __construct(string $type, array $children = [], string $id = null, array $config = [], array $data = [])
	{
		$this->type   = $type;
		$this->id     = $id;
		$this->config = $config;
		$this->data   = $data;

		$this->addChildren($children);
	}

	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return Block[]
	 */
	public function getChildren(): array
	{
		return $this->children;
	}

	/**
	 * @return string|null
	 */
	public function getId(): ?string
	{
		return $this->id;
	}

	/**
	 * @param array $children
	 * @return void
	 */
	public function addChildren(array $children): void
	{
		ArrayHelper::assertItemTypes($children, Block::class);

		foreach ($children as $child) {
			$this->addChild($child);
		}
	}

	/**
	 * @param Block $child
	 * @return void
	 */
	public function addChild(Block $child): void
	{
		$child->setParent($this);
		$this->children[] = $child;
	}

	/**
	 * @return Block|null
	 */
	public function getParent(): ?Block
	{
		return $this->parent;
	}

	/**
	 * @param Block|null $parent
	 * @return void
	 */
	public function setParent(?Block $parent): void
	{
		$this->parent = $parent;
	}

	/**
	 * @return ImmutableDictionary
	 */
	public function getConfig(): ImmutableDictionary
	{
		return new ImmutableDictionary($this->config);
	}

	/**
	 * @return ImmutableDictionary
	 */
	public function getData(): ImmutableDictionary
	{
		return new ImmutableDictionary($this->data);
	}

	/**
	 * Get all parent blocks, root first, latest last
	 * @return array
	 */
	public function getParents(): array
	{
		$parents = [];
		$parent  = $this->getParent();
		while ($parent !== null) {
			array_unshift($parents, $parent);
			$parent = $parent->getParent();
		}
		return $parents;
	}

	public function getRoot(): Block
	{
		$parent = $this;
		while ($parent->getParent() !== null) {
			$parent = $parent->getParent();
		}
		return $parent;
	}

	public function findById(string $id): ?Block
	{
		if ($this->id === $id) {
			return $this;
		}

		foreach ($this->children as $child) {
			if ($child->getId() === $id) {
				return $child;
			}

			$found = $child->findById($id);
		}
	}
}