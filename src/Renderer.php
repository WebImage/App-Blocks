<?php

namespace WebImage\Blocks;

use WebImage\Blocks\Services\BlockManager;
use WebImage\View\View;
use WebImage\View\ViewFactory;
use WebImage\View\ViewManager;

class Renderer
{
	private ViewFactory $viewFactory;

	/**
	 * @param ViewFactory $viewFactory
	 */
	public function __construct(ViewFactory $viewFactory)
	{
		$this->viewFactory = $viewFactory;
	}

	public function render(BlockManager $blockManager, Block $block): View
	{
		$viewName = 'blocks/types/' . $block->getType();

		return $this->viewFactory->create($viewName, [
			'block'        => $block,
			'blockManager' => $blockManager,
			'renderer'     => $this,
		]);
	}
}