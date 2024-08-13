<?php

namespace WebImage\Blocks\Processing;

interface ProcessorInterface
{
	/**
	 * @param BlockFile[] $blockFiles
	 * @return void
	 */
	public function process(array $blockFiles): void;
}