<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\DateTime;

/**
 * Model, ktory sa stara o tabulku devices
 * 
 * Posledna zmena 19.07.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class PV_Devices {

  use Nette\SmartObject;

  /** @var string */
  protected $tableName = 'devices';

  /** @var Database\Table\Selection */
	private $devices;

  /** @var Database\Table\Selection */
	private $sensors;

  public function __construct(Nette\Database\Explorer $database) {
		$this->devices = $database->table("devices");
    $this->sensors = $database->table("sensors");
	}

  public function getDevicesUser( $userId ) : VDevices {
    $rc = new VDevices();

    // nacteme zarizeni
    
    $result = $this->devices->where(['user_id'=>$userId])->order('name ASC');

    foreach ($result as $row) {
      $dev = new VDevice( $row->toArray() );
      $dev->attrs['problem_mark'] = false;
      if( $dev->attrs['last_bad_login'] != NULL ) {
        if( $dev->attrs['last_login'] != NULL ) {
          $lastLoginTs = (DateTime::from( $dev->attrs['last_login']))->getTimestamp();
          $lastErrLoginTs = (DateTime::from(  $dev->attrs['last_bad_login']))->getTimestamp();
          if( $lastErrLoginTs >  $lastLoginTs ) {
            $dev->attrs['problem_mark'] = true;
          }
        } else {
          $dev->attrs['problem_mark'] = true;
        }
      }
      $rc->add( $dev );
    }
    
    // a k nim senzory

    $result = $this->sensors->where(['device_id.user_id'=>$userId])->order('name ASC');

    foreach ($result as $row) {
      $r = $row->toArray();
      $device = $rc->get( $r['device_id'] );
      $r['warningIcon'] = 0;
      $r['dc_desc'] = $row->device_class->desc;
      $r['unit'] = $row->value_type->unit;
      dump($row->value_type->unit);
      dumpe($r);
      if( $r['last_data_time'] ) {
        $utime = (DateTime::from( $r['last_data_time'] ))->getTimestamp();
        if( time()-$utime > $r['msg_rate'] ) {
          $r['warningIcon'] = ( $device->attrs['monitoring']==1 ) ? 1 : 2;
        } 
      }
      
      if( isset($device) ) {
        $device->addSensor( $r );
      }
    }

    return $rc;
  }

} // End class PV_Devices

class VDevices {
  use Nette\SmartObject;

  public $devices = [];
  
  public function add( VDevice $device )
  {
      $this->devices[ $device->attrs['id'] ] = $device;
  }

  public function get( $id ) : VDevice
  {
      return $this->devices[$id];
  }
}

class VDevice {
  use Nette\SmartObject;

  /**
   * 	id	passphrase	name	desc	first_login	last_login
   */
  public $attrs;

  /**
   * Pole poli s indexy
   * id	device_id	channel_id	name	device_class	value_type	msg_rate	desc	display_nodata_interval	preprocess_data	preprocess_factor	dc_desc	unit
   */
  public $sensors = [];

  public function __construct( $attrs ) {
    $this->attrs = $attrs;
  }
  
  public function addSensor( $sensorAttrs ) {
      $this->sensors[ $sensorAttrs['id'] ] = $sensorAttrs;
  }
}