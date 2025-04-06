<?php

namespace App\ApiModule\Presenters;

use App\Services;

/**
 * Domáci presenter pre API.
 * Posledna zmena(last change): 27.10.2023
 *
 * Modul: API
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.1
 */
class HomepagePresenter extends BasePresenter
{

	private $config;

	public function __construct(array $parameters, Services\ApiConfig $config)
	{
		// Nastavenie z config-u
		$this->nastavenie = $parameters;
		$this->config = $config;
	}

	public function actionDefault(): void
	{
		//$this->sendJson($this->units->getUnits());
	}

	public function actionMyAppSettings(): void
	{
		$out = $this->config->getConfigs();
		$out['basePath'] = $this->template->basePath;
		$this->sendJson($out);
	}
}
