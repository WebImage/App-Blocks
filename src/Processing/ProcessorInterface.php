<?php

namespace WebImage\BlockManager\Processing;

interface ProcessorInterface
{
	/**
	 * @param BlockFile[] $blockFiles
	 * @return void
	 */
	public function process(array $blockFiles): void;
}