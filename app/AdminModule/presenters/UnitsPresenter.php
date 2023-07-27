<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Model;

/**
 * Units presenter
 * Last change 09.06.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.5
 */
class UnitsPresenter extends BasePresenter
{
	// Database tables	
	/** @var Model\PV_Units @inject */
	public $units;

	public function renderDefault()
	{
		$this->template->units = $this->units->getUnits();
	}
}
