<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Model;
use App\Services\Logger;
use Nette;
use Nette\Application\UI\Form;

/**
 * Presenter pre prácu so senzormi
 * Posledna zmena 09.06.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2022 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.4
 */

final class SensorPresenter extends BasePresenter
{
	use Nette\SmartObject;

	/** @persistent */
	public $viewid = "";

	/** @var Model\PV_Devices @inject */
	public $devices;
	/** @var Model\PV_Sensors @inject */
	public $sensors;

	public function __construct($parameters)
	{
		$this->links = $parameters['links'];
		$this->appName = $parameters['title'];
	}


	protected function createComponentSensorForm(): Form
	{
		$form = new Form;
		$form->addProtection();

		$form->addGroup('Základní údaje');

		$form->addTextArea('desc', 'Popis:')
			->setHtmlAttribute('rows', 4)
			->setHtmlAttribute('cols', 50)
			->setRequired();

		$form->addInteger('display_nodata_interval', 'Maximální interval dat [s]:')
			->setHtmlAttribute('size', 20)
			->setDefaultValue(7200)
			->setOption('description', 'Pokud budou zaznamenaná data s větším rozestupem, nebudou v grafu spojena čárou; graf bude přerušen.')
			->setRequired();

		$form->addGroup('Násobící faktor');

		$form->addCheckbox('preprocess_data', 'Násobit naměřenou hodnotu')
			->setOption('description', 'Pokud je zaškrtnuto, bude se naměřená hodnota násobit udaným koeficientem.')
			->addCondition($form::EQUAL, true)
			->toggle('factor-id');

		$form->addText('preprocess_factor', 'Koeficient:')
			->setHtmlAttribute('size', 20)
			->addRule(Form::FLOAT, 'Musí být číslo')
			->setOption('id', 'factor-id')
			->addConditionOn($form['preprocess_data'], Form::EQUAL, true)
			->setRequired('Musí být vyplněno');

		$form->addSubmit('send', 'Uložit')
			->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

		$form->onSuccess[] = [$this, 'sensorFormSucceeded'];

		$this->makeBootstrap4($form);
		return $form;
	}


	protected function createComponentSensorextForm(): Form
	{
		$form = new Form;

		$form->addProtection();

		$form->addGroup('Základní údaje');

		$form->addTextArea('desc', 'Popis:')
			->setHtmlAttribute('rows', 4)
			->setHtmlAttribute('cols', 50)
			->setRequired();

		$form->addInteger('display_nodata_interval', 'Maximální interval dat [s]:')
			->setHtmlAttribute('size', 20)
			->setDefaultValue(7200)
			->setOption('description', 'Pokud budou zaznamenaná data s větším rozestupem, nebudou v grafu spojena čárou; graf bude přerušen.')
			->setRequired();

		$form->addGroup('Násobící faktor');

		$form->addCheckbox('preprocess_data', 'Násobit naměřenou hodnotu')
			->setOption('description', 'Pokud je zaškrtnuto, bude se naměřená hodnota násobit udaným koeficientem.')
			->addCondition($form::EQUAL, true)
			->toggle('factor-id');

		$form->addText('preprocess_factor', 'Koeficient:')
			->setHtmlAttribute('size', 20)
			->addRule(Form::FLOAT, 'Musí být číslo')
			->setOption('id', 'factor-id')
			->addConditionOn($form['preprocess_data'], Form::EQUAL, true)
			->setRequired('Musí být vyplněno');


		$form->addGroup('Poplach při vysoké hodnotě');

		$form->addCheckbox('warn_max', 'Zapnout zasílání')
			->setOption('description', 'Pokud je zaškrtnuto, bude se zasílat notifikace při dosažení či překročení maxima.')
			->addCondition($form::EQUAL, true)
			->toggle('warn_max_val')
			->toggle('warn_max_val_off')
			->toggle('warn_max_text')
			->toggle('warn_max_after');

		$form->addText('warn_max_val', 'Spustit varování při:')
			->setOption('description', 'Poplach se generuje při překročení této hodnoty.')
			->setHtmlAttribute('size', 20)
			->addRule(Form::FLOAT, 'Musí být číslo')
			->setOption('id', 'warn_max_val')
			->addConditionOn($form['warn_max'], Form::EQUAL, true)
			->setRequired('Musí být vyplněno');

		$form->addText('warn_max_val_off', 'Vypnout varování při:')
			->setOption('description', 'Poplach končí při poklesu hodnoty pod tento limit. Doporučujeme nastavit o trochu méně, než je hodnota, při které se varování spouští - aby při oscilaci naměřené hodnoty kolem limitu nechodila jedna notifikace za druhou (tzv. hystereze).')
			->setHtmlAttribute('size', 20)
			->addRule(Form::FLOAT, 'Musí být číslo')
			->setOption('id', 'warn_max_val_off')
			->addConditionOn($form['warn_max'], Form::EQUAL, true)
			->setRequired('Musí být vyplněno');

		$form->addText('warn_max_after', 'Notifikaci poslat po:')
			->setOption('description', 'Počet sekund, po které musí být hodnota nad limitem, aby byla notifikace zaslána. 0 = hned.')
			->setHtmlAttribute('size', 20)
			->addRule(Form::INTEGER, 'Musí být číslo')
			->setOption('id', 'warn_max_after')
			->addConditionOn($form['warn_max'], Form::EQUAL, true)
			->setRequired('Musí být vyplněno');

		$form->addText('warn_max_text', 'Informační text (max):')
			->setOption('description', 'Tento text bude součástí varování')
			->setHtmlAttribute('size', 50)
			->setOption('id', 'warn_max_text');


		$form->addGroup('Poplach při nízké hodnotě');

		$form->addCheckbox('warn_min', 'Zapnout zasílání')
			->setOption('description', 'Pokud je zaškrtnuto, bude se zasílat notifikace při dosažení či překročení minima.')
			->addCondition($form::EQUAL, true)
			->toggle('warn_min_val')
			->toggle('warn_min_val_off')
			->toggle('warn_min_text')
			->toggle('warn_min_after');

		$form->addText('warn_min_val', 'Spustit varování při:')
			->setOption('description', 'Poplach se generuje při poklesu hodnoty pod tento limit.')
			->setHtmlAttribute('size', 20)
			->addRule(Form::FLOAT, 'Musí být číslo')
			->setOption('id', 'warn_min_val')
			->addConditionOn($form['warn_min'], Form::EQUAL, true)
			->setRequired('Musí být vyplněno');

		$form->addText('warn_min_val_off', 'Vypnout varování při:')
			->setOption('description', 'Poplach končí při nárůstu hodnoty nad tento limit. Doporučujeme nastavit o trochu více, než je hodnota, při které se varování spouští - aby při oscilaci naměřené hodnoty kolem limitu nechodila jedna notifikace za druhou (tzv. hystereze).')
			->setHtmlAttribute('size', 20)
			->addRule(Form::FLOAT, 'Musí být číslo')
			->setOption('id', 'warn_min_val_off')
			->addConditionOn($form['warn_min'], Form::EQUAL, true)
			->setRequired('Musí být vyplněno');

		$form->addText('warn_min_after', 'Notifikaci poslat po:')
			->setOption('description', 'Počet sekund, po které musí být hodnota nad limitem, aby byla notifikace zaslána. 0 = hned.')
			->setHtmlAttribute('size', 20)
			->addRule(Form::INTEGER, 'Musí být číslo')
			->setOption('id', 'warn_min_after')
			->addConditionOn($form['warn_min'], Form::EQUAL, true)
			->setRequired('Musí být vyplněno');

		$form->addText('warn_min_text', 'Informační text (min):')
			->setOption('description', 'Tento text bude součástí varování')
			->setHtmlAttribute('size', 50)
			->setOption('id', 'warn_min_text');



		$form->addSubmit('send', 'Uložit')
			->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

		$form->onSuccess[] = [$this, 'sensorFormSucceeded'];

		$this->makeBootstrap4($form);
		return $form;
	}


	public function actionEdit(int $id): void
	{
		$post = $this->sensors->getSensor($id);
		if (!$post) {
			Logger::log(
				'audit',
				Logger::ERROR,
				"Uzivatel #{$this->getUser()->id} {$this->getUser()->getIdentity()->username} zkusil pristoupit k cizimu sensoru {$id}"
			);
			$this->error('Senzor nebyl nalezeno');
		}
		$this->checkAcces($post->device->user_id, 'senzor');

		$this->template->name = "{$post->device->name}:{$post->name}";
		$this->template->id = $id;
		$this->template->device_class = $post->id_device_classes;

		$this['sensorForm']->setDefaults($post);
		$this['sensorextForm']->setDefaults($post);
	}

	public function sensorFormSucceeded(Form $form, array $values): void
	{
		$id = $this->getParameter('id');
		if ($id) {
			// editace
			$post = $this->sensors->getSensor($id);
			if (!$post) {
				Logger::log(
					'audit',
					Logger::ERROR,
					"Uzivatel #{$this->getUser()->id} {$this->getUser()->getIdentity()->username} zkusil pristoupit k cizimu sensoru {$id}"
				);
				$this->error('Zařízení nebylo nalezeno');
			}
			$this->checkAcces($post->device->user_id, 'senzor');
			$this->sensors->updateSensor($id, $values);
		}

		$this->flashRedirect(['Device:show', $post->device->device_id], "Změny provedeny.", 'success');
	}

	public function renderShow(int $id): void
	{
		$post = $this->sensors->getSensor($id);
		if (!$post) {
			Logger::log(
				'audit',
				Logger::ERROR,
				"Uzivatel #{$this->getUser()->id} {$this->getUser()->getIdentity()->username} zkusil pristoupit k cizimu sensoru {$id}"
			);
			$this->error('Zařízení nebylo nalezeno');
		}
		$this->checkAcces($post->device->user_id, 'senzor');

		$this['menu']->addNode(2, [
			'name' => "· Zařízení {$post->device->name}",
			'link' => $this->link("Device:show", $post->device_id),
			'id'   => $post->device_id
		]);
		$this['menu']->addNode(2, [
			'name' => "· · Senzor {$post->name}",
			'link' => $this->link("Sensor:show", $id),
			'id'   => $id
		]);
		$this['menu']->addNode(2, [
			'name' => "· · · Statistika",
			'link' => $this->link(":Chart:sensorstat", $id),
			'id'   => $id
		]);
		$this['menu']->addNode(2, [
			'name' => "· · · Graf",
			'link' => $this->link(":Chart:sensor", $id),
			'id'   => $id
		]);
		$this->template->name = $post->device->name . ":" . $post->name;
		$this->template->sensor = $post;
	}
}
