<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;


class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator;
		$appDir = dirname(__DIR__);

		//$configurator->setDebugMode('192.168.32.242'); // enable for your remote IP  '94.113.255.162'
		//$configurator->setDebugMode(FALSE);
		//$configurator->setDebugMode(TRUE);
		$configurator->enableTracy($appDir . '/log');

		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory($appDir . '/temp');

		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

		$configurator->addConfig(__DIR__ . '/config/common.neon');
		if (file_exists(__DIR__ . '/config/local.neon')) {
			$configurator->addConfig(__DIR__ . '/config/local.neon');
		}

		return $configurator;
	}
}
