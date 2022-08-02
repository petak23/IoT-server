<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Model;
use App\Services;

/**
 * Units presenter
 * Last change 27.06.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
class UnitsPresenter extends BaseAdminPresenter
{
  // Database tables	
  /** @var Model\PV_Units @inject */
	public $units;

  public function __construct($parameters) {
    $this->links = $parameters['links'];
		$this->appName = $parameters['title'];
  }

  public function renderDefault() {
    $this->template->units = $this->units->getUnits();
  }
}