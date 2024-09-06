<?php

use WebImage\Blocks\RendererServiceProvider;

return [
	'serviceManager' => [
		'providers' => [
			RendererServiceProvider::class
		]
	],
	'views' => [
		'paths' => [
			__DIR__ . '/../resources/views'
		]
	]
];