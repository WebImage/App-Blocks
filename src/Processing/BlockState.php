<?php

namespace WebImage\BlockManager\Processing;

class BlockState {
	/** @var BlockFile[] */
	private array $results;
	public function __construct(array $results)
	{
		$this->results = $results;
	}

//	/**
//	 * @return BlockFile[]
//	 */
//	public function getResults(): array
//	{
//		return $this->results;
//	}

	/**
	 * @param callable(BlockState): string $callable
	 */
//	public function pipe(callable $callable): BlockState
//	{
////		$this->results = array_map(function(BlockFile $file) use ($callable) {
////			// Callable with function(BlockFile, BlockState) callback
//////			$result = $file->withResult(call_user_func($callable, $file, $this, ++$ix));
////			return call_user_func($callable, $file, $this, ++$ix);
////
////			return $result;
////		}, $this->results);
//		$results = call_user_func($callable, $this->results);
//		if (!is_array($results)) {
//			throw new \InvalidArgumentException('BlockState::pipe(callable) must return an array of BlockFile objects');
//		}
//
//		// Ensure that an array of BlockFile's are returned
//		ArrayHelper::assertItemTypes($results, BlockFile::class);
//		$this->results = $results;
//
//		return $this;
//	}

//	/**
//	 * @param callable(BlockFile[]): void $callable
//	 * @return $this
//	 */
//	public function destination(callable $callable): BlockState
//	{
//		$this->assertFileNamesChanged();
//		call_user_func($callable, $this->results);
//		return $this;
//	}
	public function process(ProcessorInterface $destination): BlockState
	{
		$destination->process($this->results);
		return $this;
	}

	/**
	 * Make sure that we do not accidentally overwrite the original files (i.e. that the file name has changed from the original filename)
	 */
	private function assertFileNamesChanged(): void
	{
		foreach($this->results as $result) {
			if ($result->getFile() === $result->getOriginalFile()) {
				throw new \RuntimeException('File name has not changed: ' . $result->getOriginalFile());
			}
		}
	}
}