<?php

declare(strict_types=1);

namespace App\Model;

use App\Model;
use Nette;
use Nette\Utils\DateTime;

/**
 * Model, ktory sa stara o tabulku devices
 * 
 * Posledna zmena 11.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
class PV_Devices
{
  use Nette\SmartObject;

  /** @var Database\Table\Selection */
  private $devices;

  /** @var Database\Table\Selection */
  private $sensors;

  private $pv_sensors;

  public function __construct(Nette\Database\Explorer $database, Model\PV_Sensors $pv_sensors)
  {
    $this->devices = $database->table("devices");
    $this->pv_sensors = $pv_sensors;
  }

  public function getDevicesUser($userId): VDevices
  {
    $rc = new VDevices();
    // načítame zariadenia

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
      // Pridám zariadenie a k nemu načítam senzory
      $rc->addWithSensors($dev, $this->pv_sensors->getDeviceSensors($row->id, $row->monitoring));
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
} 
// ------------------------------------  End class PV_Devices

/** 
 * Objekt všetkých zariadení 
 * */
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

  /** Pridanie zariadenia aj so senzormi */
  public function addWithSensors(VDevice $device, ?Nette\Database\Table\Selection $sensors): void
  {
    $this->devices[$device->attrs['id']] = $device;
    if ($sensors != null && $sensors->count()) {
      foreach ($sensors as $s) {
        $this->devices[$device->attrs['id']]->addSensor($s);
      }
    }
  }
}

/** 
 * Objekt jedného zariadenia 
 * */
class VDevice
{
  use Nette\SmartObject;

  /** @var Nette\Database\Table\ActiveRow Kompletné data o zariadení */
  public $attrs;

  /** @var bool Príznak problému */
  public $problem_mark = false;

  /** @var array Pole senzorov zariadenia */
  public $sensors = [];

  public function __construct(Nette\Database\Table\ActiveRow $attrs)
  {
    $this->attrs = $attrs;
  }

  public function addSensor(Nette\Database\Table\ActiveRow $sensorAttrs): void
  {
    $this->sensors[$sensorAttrs->id] = $sensorAttrs;
  }
}
