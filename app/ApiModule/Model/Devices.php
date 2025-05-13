<?php

declare(strict_types=1);

namespace App\ApiModule\Model;

use App\Model;
use App\Services\Logger;
use Nette;
use Nette\Database;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

/**
 * Model, ktorý sa stará o tabuľku devices
 * 
 * Posledna zmena 25.04.2025
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2025 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.8
 */
class Devices
{
	use Nette\SmartObject;

	/** @var Database\Table\Selection */
	private $devices;
	/** @var Database\Table\Selection */
	private $sessions;
	/** @var Database\Table\Selection */
	private $measures;
	/** @var Database\Table\Selection */
	private $sumdata;
	/** @var Database\Table\Selection */
	private $updates;
	/** @var Database\Table\Selection */
	private $pv_blobs;

	/** @var Database\Table\Selection */
	private $sensors;

	private $pv_sensors;

	public function __construct(
		Database\Explorer $database,
		Sensors $pv_sensors,
		Sessions $sessions,
		Model\PV_Updates $pv_updates,
		Model\PV_Blobs $pv_blobs
	) {
		$this->devices = $database->table("devices");
		$this->measures = $database->table("measures");
		$this->sumdata = $database->table("sumdata");
		$this->updates = $pv_updates;
		$this->sessions = $sessions;
		$this->pv_sensors = $pv_sensors;
		$this->pv_blobs = $pv_blobs;
	}

	public function getDevicesUser(int $userId, bool $return_as_array = false): VDevices|array
	{
		$rc = new VDevices();
		// načítame zariadenia

		$result = $this->devices->where(['user_id' => $userId])->order('id ASC');

		foreach ($result as $row) {
			$dev = new VDevice($row, $return_as_array);
			if ($dev->attrs['last_bad_login'] != NULL) {
				if ($dev->attrs['last_login'] != NULL) {
					$lastLoginTs = (DateTime::from($dev->attrs['last_login']))->getTimestamp();
					$lastErrLoginTs = (DateTime::from($dev->attrs['last_bad_login']))->getTimestamp();
					if ($lastErrLoginTs >  $lastLoginTs) {
						$dev->problem_mark = true;
					}
				} else {
					$dev->problem_mark = true;
				}
			}
			// Pridám zariadenie a k nemu načítam senzory
			$rc->addWithSensors($dev, $this->pv_sensors->getDeviceSensors($row->id, $row->monitoring), $return_as_array);
		}
		return $return_as_array ? $rc->returnAsArray(false) : $rc;
	}

	/** Pridanie zariadenia */
	public function createDevice($values, bool $return_as_array = false): ActiveRow|array
	{
		$d = $this->devices->insert($values);
		$d = $return_as_array ? $d->toArray() : $d;
		return $d;
	}

	/** Info o zariadení */
	public function getDevice(
		int $deviceId,
		bool $with_sensors = false,
		bool $return_as_array = false
	): VDevice|array {

		if (($_t = $this->devices->get($deviceId)) == null) {	// Načítanie zariadenia z DB
			return ['error' => "Device not found", 'error_n' => 1, 'device_id' => $deviceId];
		}

		$d = new VDevice($_t); // Vytvor zariadenie
		if ($with_sensors) {
			// Pridám zariadenie a k nemu načítam senzory
			$sensors = $this->pv_sensors->getDeviceSensors($deviceId, $d->attrs->monitoring);
			if ($sensors != null && $sensors->count()) {
				foreach ($sensors as $s) {
					$d->addSensor($s, $return_as_array);
				}
			}
			$lastLoginTs = (DateTime::from($d->attrs->last_login))->getTimestamp();
			$lastTime = $lastLoginTs;
			foreach ($d->sensors as $sensor) {
				if ($sensor['last_data_time']) {
					$utime = (DateTime::from($sensor['last_data_time']))->getTimestamp();
					if (!$lastTime || ($utime > $lastTime)) {
						$lastTime = $utime;
					}
				}
			}
		}

		if ($return_as_array) {
			$_d = $d->attrs->toArray();
			$_d['problem_mark'] = $d->problem_mark;
			$_d['sensors'] = $d->sensors;
			$_d['first_login'] = $_d['first_login'] != null ? $_d['first_login']->format('d.m.Y H:i:s') : null;
			$_d['last_login'] = $_d['last_login'] != null ? $_d['last_login']->format('d.m.Y H:i:s') : null;
			$_d['lastComm'] = DateTime::from($lastTime)->format('d.m.Y H:i:s');
			if ($d->attrs->uptime) {
				$_d['uptime'] = $this->secondsToTime($d->attrs->uptime);
			}
			$_d['updates'] = $this->updates->getOtaUpdates($deviceId);

			$_d['problem_mark'] = false;
			if ($d->attrs->last_bad_login != NULL) {
				if ($d->attrs->last_login != NULL) {
					$lastErrLoginTs = (DateTime::from($d->attrs->last_bad_login))->getTimestamp();
					if ($lastErrLoginTs >  $lastLoginTs) {
						$_d['problem_mark'] = true;
					}
				} else {
					$_d['problem_mark'] = true;
				}
			}
			$_d['subory'] = $this->pv_blobs->getBlobCount($deviceId);

			$d = $_d;
		}
		return $d;
	}

	public function deleteDevice($id)
	{
		Logger::log('webapp', Logger::DEBUG,  "Delete session device {$id}");

		// Najprv zmeniť heslo a zmazať session, aby se už nemohlo prihlásiť
		$this->devices->get($id)->update(['passphrase' => 'x']);
		$this->sessions->deleteSession($id);

		$sens = $this->pv_sensors->getDeviceSensors($id);

		// Zmazať data
		if ($sens->count()) {
			Logger::log('webapp', Logger::DEBUG,  "Delete measures device {$id}");
			$this->measures->where("sensor_id", $sens)->delete();

			Logger::log('webapp', Logger::DEBUG,  "Delete sumdata device {$id}");

			$this->sumdata->where("sensor_id in ?", $sens)->delete();

			Logger::log('webapp', Logger::DEBUG,  "Delete device {$id}");

			// Zmazať senzory zariadenia
			$sens->delete();
		}



		$this->devices->get($id)->delete();
		
		Logger::log('webapp', Logger::DEBUG,  "Delete OK.");
	}

	private function secondsToTime($inputSeconds)
	{
		$secondsInAMinute = 60;
		$secondsInAnHour = 3600;
		$secondsInADay = 86400;

		// Extract days
		$days = floor($inputSeconds / $secondsInADay);

		// Extract hours
		$hourSeconds = $inputSeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);

		// Extract minutes
		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);

		// Extract the remaining seconds
		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);

		// Format and return
		$timeParts = [];
		$sections = [
			'd' => (int)$days,
			'hod' => (int)$hours,
			'min' => (int)$minutes,
			'sec' => (int)$seconds,
		];

		foreach ($sections as $name => $value) {
			if ($value > 0) {
				$timeParts[] = $value . ' ' . $name;
			}
		}

		return implode(', ', $timeParts);
	}
}
// ------------------------------------  End class Devices

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
	public function addWithSensors(
		VDevice $device,
		Nette\Database\Table\Selection $sensors,
		bool $return_sensors_as_array = false
	): void {
		$this->devices[$device->attrs['id']] = $device;
		if ($sensors != null && $sensors->count()) {
			foreach ($sensors as $s) {
				$this->devices[$device->attrs['id']]->addSensor($s, $return_sensors_as_array);
			}
		}
	}

	public function returnAsArray(bool $with_index = true): array
	{
		$out = [];
		foreach ($this->devices as $k => $v) {
			if ($with_index) {
				$out[$k] = $v->attrs;
				$out[$k]['problem_mark'] = $v->problem_mark;
				$out[$k]['sensors'] = $v->sensors;
			} else {
				$out[] = array_merge($v->attrs, [
					'problem_mark' => $v->problem_mark,
					'sensors' => $v->sensors
				]);
			}
		}
		return $out;
	}
}

/** 
 * Objekt jedného zariadenia 
 * */
class VDevice
{
	use Nette\SmartObject;

	/** @var Nette\Database\Table\ActiveRow|array Kompletné data o zariadení */
	public $attrs;

	/** @var bool Príznak problému */
	public $problem_mark = false;

	/** @var array Pole senzorov zariadenia */
	public $sensors = [];

	public function __construct(Nette\Database\Table\ActiveRow $attrs, bool $return_as_array = false)
	{
		$this->attrs = $return_as_array ? $attrs->toArray() : $attrs;
	}

	public function addSensor(Nette\Database\Table\ActiveRow $sensorAttrs, bool $return_as_array = false): void
	{
		if ($return_as_array) {
			$out = array_merge(
				[
					'value_unit' => $sensorAttrs->value_types->unit,
					'dc_desc'	=> $sensorAttrs->device_classes->desc,
				],
				$sensorAttrs->toArray()
			);
		}
		$this->sensors[$sensorAttrs->id] = $return_as_array ? $out : $sensorAttrs;
	}
}
