<?php

namespace WebImage\BlockManager\Processor;

use WebImage\BlockManager\BlockFile;

class BlockState {
	private array $results;
	public function __construct(array $results)
	{
		$this->results = $results;
	}

	public function pipe(callable $callable): \WebImage\BlockManager\BlockState
	{
		$this->results = array_map(function(BlockFile $file) use ($callable) {
			return $file->withResult(call_user_func($callable, $file->getResult()));
		}, $this->results);

		return $this;
	}

	public function destination(callable $callable): BlockState
	{
		call_user_func($callable, $this->results);
		return $this;
	}
}