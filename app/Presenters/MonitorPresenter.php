<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model;
use Nette;

/**
 * Presenter pre prácu s monitoringom
 * Posledna zmena 14.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2022 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
final class MonitorPresenter extends BaseAdminPresenter
{
  use Nette\SmartObject;

  // Database tables
  /** @var Model\PV_Devices @inject */
  public $devices;

  // http://lovecka.info/ra/monitor/show/aaabbb/2/
  public function renderShow($id, $token)
  {
    $userData = $this->user_main->getUser($id);
    if (!$userData) {
      $this->error('Zařízení nebylo nalezeno');
    }
    if (!$token || ($userData['monitoring_token'] !== $token)) {
      $this->error('Token nesouhlasí.');
    }
    $this->template->devices = $this->devices->getDevicesUser($id);

    $response = $this->getHttpResponse();
    $response->setHeader('Cache-Control', 'no-cache');
    $response->setExpiration('1 sec');
  }
}
