<?php

namespace WebImage\BlockManager\Processing;

class BlockFile {
	private string $originalFile;
	private string $file;
	private /* mixed */ $result;

	/**
	 * @param string $file
	 * @param $result
	 */
	public function __construct(string $file, $result)
	{
		$this->originalFile   = $file;
		$this->file   = $file;
		$this->result = $result;
	}

	public function getOriginalFile()
	{
		return $this->originalFile;
	}

	public function getFile(): string
	{
		return $this->file;
	}

	public function getResult()
	{
		return $this->result;
	}

	public function withResult($result, string $file=null)
	{
		$result = new BlockFile($file === null ? $this->getFile() : $file, $result);
		$result->originalFile = $this->originalFile;
		return $result;
	}
}