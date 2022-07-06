<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\DateTime;

/**
 * Model, ktory sa stara o tabulku devices
 * 
 * Posledna zmena 04.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class PV_Devices
{
  use Nette\SmartObject;

  /** @var Database\Table\Selection */
  private $devices;

  /** @var Database\Table\Selection */
  private $sensors;

  public function __construct(Nette\Database\Explorer $database)
  {
    $this->devices = $database->table("devices");
    $this->sensors = $database->table("sensors");
  }

  public function getDevicesUser($userId): VDevices
  {
    $rc = new VDevices();

    // nacteme zarizeni

    $result = $this->devices->where(['user_id' => $userId])->order('name ASC');

    foreach ($result as $row) {
      $dev = new VDevice($row);
      if ($dev->attrs->last_bad_login != NULL) {
        if ($dev->attrs->last_login != NULL) {
          $lastLoginTs = (DateTime::from($dev->attrs->last_login))->getTimestamp();
          $lastErrLoginTs = (DateTime::from($dev->attrs->last_bad_login))->getTimestamp();
          if ($lastErrLoginTs >  $lastLoginTs) {
            $dev->problem_mark = true;
          }
        } else {
          $dev->problem_mark = true;
        }
      }
      $rc->add($dev);
    }

    // a k nim senzory

    $result = $this->sensors->where(['device_id.user_id' => $userId])->order('name ASC');

    foreach ($result as $row) {
      $device = $rc->get($row->device_id); // Nájdem príslušné zariadenie
      $r['warningIcon'] = 0;
      $r['sensor'] = $row;
      if ($row->last_data_time) {
        $utime = (DateTime::from($row->last_data_time))->getTimestamp();
        if (time() - $utime > $row->msg_rate) {
          $r['warningIcon'] = ($device->attrs['monitoring'] == 1) ? 1 : 2;
        }
      }

      if (isset($device)) {
        $device->addSensor($r);
      }
    }

    return $rc;
  }

  /** 
   * Pridanie zariadenia
   *    */
  public function createDevice($values)
  {
    return $this->devices->insert($values);
  }

  /** 
   * Pridanie zariadenia
   *    */
  public function getDevice(int $deviceId): ?Nette\Database\Table\ActiveRow
  {
    return $this->devices->get($deviceId);
  }
} // End class PV_Devices

/** Objekt všetkých zariadení */
class VDevices
{
  use Nette\SmartObject;

  /** @var array Pole všetkých zariadení */
  public $devices = []; 

  public function add(VDevice $device): void
  {
    $this->devices[$device->attrs['id']] = $device;
  }

  public function get(int $id): VDevice
  {
    return $this->devices[$id];
  }
}

/** Objekt jedného zariadenia */
class VDevice
{
  use Nette\SmartObject;

  /** @var Nette\Database\Table\ActiveRow Kompletné data o zariadení */
  public $attrs;

  /** @var bool Príznak problému */
  public $problem_mark = false;

  /**
   * Pole senzorov zariadenia
   * id	device_id	channel_id	name	id_device_classes	value_type	msg_rate	desc	display_nodata_interval	preprocess_data	preprocess_factor	dc_desc	unit
   */
  public $sensors = [];

  public function __construct($attrs)
  {
    $this->attrs = $attrs;
  }

  public function addSensor($sensorAttrs)
  {
    $this->sensors[$sensorAttrs['sensor']->id] = $sensorAttrs;
  }
}
