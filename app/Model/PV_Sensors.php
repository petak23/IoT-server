<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
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

  /**
   * Info o senzore
   */
  public function getSensor(int $sensorId): ?ActiveRow
  {
    /*return $this->database->fetch('
            select 
                s.*, 
                d.name as dev_name, d.desc as dev_desc, d.user_id,  
                vt.unit
            from sensors s

            left outer join devices d
            on s.device_id = d.id

            left outer join value_types vt
            on s.id_value_types = vt.id
            
            where s.id = ?
        ', $sensorId);*/
    return $this->find($sensorId);
  }

  /**
   * sensor_id	pocet	name	desc
   */
  public function getDataStatsMeasures($id)
  {
    return $this->connection->fetchAll('
      select d.*, s.name, s.desc
      from (
        select sensor_id, count(*) as pocet
        from measures
        where sensor_id in (select id from sensors where device_id = ?)
        group by sensor_id
      ) d
        
      left outer join sensors s on d.sensor_id = s.id
        
      order by s.name
    ', $id);
  }

  /**
   * sensor_id	pocet	name	desc
   */
  public function getDataStatsSumdata($id)
  {
    return $this->connection->fetchAll('
            select 
            d.*, s.name, s.desc
            from 
            (
            select sensor_id, count(*) as pocet
            from sumdata
            where 
            sensor_id in (select id from sensors where device_id = ?)
            group by sensor_id
            ) d
            
            left outer join sensors s
            on d.sensor_id = s.id
            
            order by s.name
        ', $id);
  }
}
