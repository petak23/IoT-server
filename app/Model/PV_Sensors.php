<?php

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku sensors
 * 
 * Posledna zmena 08.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class PV_Sensors extends Table
{

  /** @var string */
  protected $tableName = 'sensors';

  public function getDeviceSensors(int $deviceId)
  {
    return $this->findBy(['device_id' => $deviceId])->order('id ASC');
    /*return $this->database->fetchAll('
            select s.*,
            vt.unit
            from  sensors s
            left outer join value_types vt
            on s.id_value_types = vt.id
            where device_id = ?
            order by id asc
        ', $deviceId);*/
  }
}
