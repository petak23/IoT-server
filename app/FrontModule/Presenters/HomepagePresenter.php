<?php

namespace App\FrontModule\Presenters;

//use App\ApiModule\Model;

/**
 * Domáci presenter pre Front modul.
 * Posledna zmena(last change): 02.10.2023
 *
 * Modul: Front
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
class HomepagePresenter extends BasePresenter
{
	public function renderDefault(string $path = "", int $id = 0): void
	{
		$this->template->vue_path = $path;
		$this->template->vue_id = $id;
	}
}
