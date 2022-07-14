<?php

namespace App\Model;

use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

/**
 * Model, ktory sa stara o tabulku sensors
 * 
 * Posledna zmena 14.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2022 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class PV_Sensors extends Table
{

  /** @var string */
  protected $tableName = 'sensors';

  public function getDeviceSensors(int $deviceId, int $monitoring = 0): Selection
  {
    $sensors = $this->findBy(['device_id' => $deviceId])->order('id ASC');

    foreach ($sensors as $sensor) {
      $warningIcon = 0;
      if ($sensor->last_data_time) {
        $utime = (DateTime::from($sensor->last_data_time))->getTimestamp();
        if (time() - $utime > $sensor->msg_rate) {
          $warningIcon = $monitoring == 1 ? 1 : 2;
        }
      }
      if ($warningIcon != $sensor->warning_icon) $sensor->update(['warning_icon' => $warningIcon]);
    }

    return $sensors;
  }
}
