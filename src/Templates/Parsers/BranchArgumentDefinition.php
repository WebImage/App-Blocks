<?php

namespace WebImage\Blocks\Templates\Parsers;

class BranchArgumentDefinition
{
    private string $name;
	private string $description;
    private bool   $required;
    private bool   $multiple;

    /**
     * @param string $name The name of the branch argument
     * @param string $description A description for the purpose of the branch argument
     * @param bool $required Whether the argument is required
     * @param bool $multiple Whether the branch argument can be specified multiple times, i.e. it must be the last argument as it works like an ...ellipse parameter and returns the value as an array
     */
    public function __construct(string $name, string $description, bool $required = true, bool $multiple = false)
    {
		$this->name        = $name;
		$this->description = $description;
		$this->required    = $required;
		$this->multiple    = $multiple;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function hasMultiple(): bool
    {
        return $this->multiple;
    }
}
