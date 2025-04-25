<?php

namespace App\ApiModule\Presenters;

use App\ApiModule\Model;

/**
 * Presenter pre pristup k api užívateľov.
 * Posledna zmena(last change): 25.04.2025
 *
 * Modul: API
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2025 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.2
 */
class DevicesPresenter extends BasePresenter
{

	// -- DB
	/** @var Model\Devices @inject */
	public $devices;
	/** @var Model\Measures @inject */
	public $measures;

	public function actionDefault(): void
	{
		$this->sendJson($this->devices->getDevicesUser($this->user->id, true));
	}

	public function actionDevice(int $id = 0): void
	{
		if ($id > 0) {
			$device = $this->devices->getDevice($id, true, true);
			if (isset($device['error_n'])) { // Zariadenie sa nenašlo
				$out = [
					'status' => 404,
					'message' => $device['error']
				];
			} else {
				$out = array_merge($device, [
					'jsonUrl'		=> $this->link('//:Json:data', ['token' => $device['json_token'], 'id' => $device['id']]),
					'jsonUrl2'	=> $this->link('//:Json:meteo', ['token' => $device['json_token'], 'id' => $device['id'], 'temp' => 'JMENO_TEMP_SENZORU', 'rain' => 'JMENO_RAIN_SENZORU']),
					'blobUrl'		=> $this->link('//:Gallery:show', ['token' => $device['blob_token'], 'id' => $device['id']]),
					'status'		=> 200
				]);
			}
		} else {
			$out = [
				'status' => 404,
				'message' => "Invalid device Id..."
			];
		}

		$this->sendJson($out);
	}

	/** Vráti zoznam senzorov pre dané zariadenie */
	public function actionSensors(int $id): void
	{
		$d = $this->devices->getDevice($id, true, true);
		$this->sendJson($d["sensors"]);
	} 

	public function actionMeasures(int $id): void
	{
		$this->sendJson($this->measures->getMeasures($id));
	}

	public function actionMeasureslast(int $id): void
	{
		$this->sendJson($this->measures->getLastMeasure($id));
	}
}
