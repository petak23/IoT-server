<?php

declare(strict_types=1);

namespace PeterVojtech\MainLayout\GoogleAnalytics;

use Nette\Application\UI\Control;
use Nette\Http\Request;

/**
 * Komponenta pre vlozenie kodu pre google analytics do stranky
 * Posledna zmena(last change): 01.08.2023
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.6
 */

class GoogleAnalyticsControl extends Control
{
	/** @var string|null */
	private $udaj;
	/** @var string */
	private $host;

	/** 
	 * @param String|null $ua_code 
	 * @param Nette\Http\Request $request */
	public function __construct(?string $ua_code = null, Request $request)
	{
		$this->udaj = $ua_code;
		$this->host = $request->getUrl()->host;
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/GoogleAnalytics.latte');
		$this->template->id_google_analytics = ($this->udaj != null & $this->host != "localhost") ? (strpos($this->udaj, "UA-") === 0 ? $this->udaj : false) : false;
		$this->template->render();
	}
}
