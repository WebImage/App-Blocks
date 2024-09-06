<?php

namespace WebImage\Blocks\Services;

interface BlockManagerFactoryInterface
{
	public function create(): BlockManager;
}