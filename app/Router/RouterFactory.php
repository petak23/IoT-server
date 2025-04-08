<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


/**
 * Router
 * Posledna zmena 08.04.2025
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2025 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */

/*final */ class RouterFactory
{
	//use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->withModule('Api')
			->addRoute('api/device/<id>[/<action>]', 'Devices:device')
			->addRoute('api/sensor/last/<id>', 'Devices:measureslast')
			->addRoute('api/devices[/<action>[/<id>]]', 'Devices:default')
			//->addRoute('inventory/<action>[/<id>]', 'Inventory:user')
			//->addRoute('sensor[/<action>[/<id>]]', 'Sensor:show')
			->addRoute('api/units[/<action>[/<id>]]', 'Units:default')
			->addRoute('api/user[/<id>[/<action>]]', 'Users:user')
			->addRoute('api/users[/<action>[/<id>]]', 'Users:default')
			//->addRoute('api/users[/<action>[/<id>]]', 'Users:default')
			->addRoute('api[/<presenter>[/<action>[/<id>]]]', 'Homepage:default');
		$router->withModule('Front')
			->addRoute('front[/<path>[/<id>]]', 'Homepage:default');

		$router->withModule('Admin')
			->addRoute('device/<action>[/<id>]', 'Device:show')
			->addRoute('inventory/<action>[/<id>]', 'Inventory:user')
			->addRoute('sensor[/<action>[/<id>]]', 'Sensor:show')
			->addRoute('units[/<action>[/<id>]]', 'Units:default')
			->addRoute('users', 'User:default')
			->addRoute('user[/<action>[/<id>]]', 'User:default')
			->addRoute('useracl[/<action>[/<id>]]', 'UserAcl:default');

		$router->addRoute('sign/reset/<id>/<new_password_key>/', 'Sign:resetPassword');
		$router->addRoute('chart/view/<token>/<id>/', 'Chart:view');
		$router->addRoute('chart/sensor/show/<id>/', 'Chart:sensor');
		$router->addRoute('chart/sensorstat/show/<id>/', 'Chart:sensorstat');
		$router->addRoute('chart/sensorchart/show/<id>/', 'Chart:sensorchart');
		$router->addRoute('chart/<action>/<token>/<id>/', 'Chart:coverage');
		$router->addRoute('json/<action>/<token>/<id>/', 'Json:data');
		$router->addRoute('gallery/<token>/<id>/', 'Gallery:show');
		$router->addRoute('gallery/<token>/<id>/<blobid>/', 'Gallery:blob');
		$router->addRoute('monitor/show/<token>/<id>/', 'Monitor:show');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
