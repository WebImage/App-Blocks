<?php

namespace WebImage\Blocks;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use WebImage\Container\ServiceProvider\AbstractServiceProvider;
use WebImage\Paths\PathManager;
use WebImage\Paths\PathManagerInterface;
use WebImage\View\EngineResolver;
use WebImage\View\ViewFactoryServiceProvider;
use WebImage\View\FileViewFinder;
use WebImage\View\ViewFactory;
use WebImage\View\ViewFinderInterface;

class RendererServiceProvider extends AbstractServiceProvider
{
	protected array $provides = [
		Renderer::class
	];

	/**
	 * @throws ContainerExceptionInterface
	 */
	public function register(): void
	{
		$this->getContainer()->add(Renderer::class, function() {
			return new Renderer($this->getViewFactory());
		});
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	private function getViewFactory(): ViewFactory
	{
		return $this->getContainer()->get(ViewFactory::class);
	}
}