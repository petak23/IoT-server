<?php

namespace App\ApiModule\Presenters;

use App\ApiModule\Model;
use App\Services;
use App\Services\Logger;

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
	/** @var \App\Model\PV_Devices @inject */
	public $pv_devices;

	private $config;

	public function __construct(array $parameters, Services\ApiConfig $config)
	{
		// Nastavenie z config-u
		$this->nastavenie = $parameters;
		$this->config = $config;
	}

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
					'status'		=> 200,
					'url'				=> $this->link('//:Homepage') // . 'ra', ??? TODO over
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

	public function actionEdit(int $id) : void {
		
		$_post = json_decode(file_get_contents("php://input"), true);
		//dumpe($_post);
		$values['name'] = $this->user->getIdentity()->prefix.":".$_post['name'];
		$values['user_id'] = $this->user->id;
		$values['passphrase'] = $this->config->encrypt( $_post['passphrase'], $_post['name'] );

		if( $id ) {
				// editace
				$device = $this->pv_devices->getDevice( $id );
				if (!$device) {
					$out = ["status" => 404, "message" => "Zariadenie sa nenašlo"];
				} else if( $this->user->id != $device->user_id ) {
					Logger::log( 'audit', Logger::ERROR , 
						sprintf("Užívateľ #%s (%s) zkúsil editovať zariadenie patriace užívateľovi #%s", $this->user->id, $this->user->getIdentity()->email, $device->user_id));
					$this->user->logout(true);
					//$form->addError($this->texts->translate('device_form_not_aut'), "danger");
					$out = ["status" => 500, "message" => "K tomuto zariadeniu nemáte oprtávnený prístup!"];
				} else {
					$device->update( $values );
					$out = ["status" => 200, "message" => "Údaje zariadenia aktualizované."];
				}
		} else {
				// zalozeni
				$this->pv_devices->createDevice( $values );
				$out = ["status" => 200, "message" => "Zariadenie bolo vytvorené."];
		}
		$this->sendJson($out);
	}
}
