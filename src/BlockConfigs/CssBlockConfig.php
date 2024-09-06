<?php

namespace WebImage\Blocks\BlockConfigs;

use WebImage\Blocks\Block;
use WebImage\Blocks\BlockConfig;

class CssBlockConfig extends BlockConfig
{
	private bool $marginControls = false;
	private bool $paddingControls = false;
	public function enableMarginControls()
	{
		$this->marginControls = true;
	}
	public function disableMarginControls()
	{
		$this->marginControls = false;
	}

	public function enablePaddingControls()
	{
		$this->paddingControls = true;
	}

	public function disablePaddingControls()
	{
		$this->paddingControls = false;
	}

	public function configureBlock(Block $block)
	{
//		$block->setConfig('css', [
//			'marginControls' => $this->marginControls,
//		]);
	}
}