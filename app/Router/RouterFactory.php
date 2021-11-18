<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


/**
 * Router
 * Posledna zmena 17.11.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */

/*final */class RouterFactory {
	//use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
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
    $router->addRoute('device/<action>[/<id>]', 'Device:show');
		$router->addRoute('useracl[/<action>[/<id>]]', 'UserAcl:default');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
