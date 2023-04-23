<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;

use Nette\Application\UI\Form;

//use App\Exceptions;
use App\Forms;
use App\Model;

use App\Services\Logger;
use App\Services;


/**
 * Presenter pre prácu s užívateľom
 * Posledna zmena 20.04.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2022 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.4
 */
final class UserPresenter extends BaseAdminAPresenter
{
	use Nette\SmartObject;

	// Database tables	
	/** @var Model\PV_Devices @inject */
	public $devices;

	/** @var Model\PV_User_state @inject */
	public $user_state;

	// Forms
	/** @var Forms\User\EditUserFormFactory @inject*/
	public $editUserForm;


	/** @var Services\InventoryDataSource */
	private $datasource;

	/** @var \App\Services\Config */
	//private $config;

	/** @var Nette\Security\Passwords */
	private $passwords;

	private $user_show;

	public function __construct(
		$parameters,
		Services\InventoryDataSource $datasource,
		Nette\Security\Passwords $passwords
	) {
		$this->datasource = $datasource;
		//$this->links = $parameters['links'];
		//$this->appName = $parameters['title'];
		$this->passwords = $passwords;
	}

	public function renderNotFound()
	{
		Logger::log('audit', Logger::ERROR, sprintf("Uzivatel %s nenalezen", $this->user_show));
		$this->template->info_text[0] = "Užívateľ sa nenašiel";
		$this->template->info_text[1] = sprintf("Užívateľ s id=%s sa v databáze nenašiel! Pravdepodobne ste zadali nesprávne id užívateľa.", $this->user_show);
	}

	/*
	id	username	phash	role	email	prefix	id_user_state	bad_pwds_count	locked_out_until	
	measures_retention	sumdata_retention	blob_retention	self_enroll	self_enroll_code	self_enroll_error_count
			cur_login_time	cur_login_ip	cur_login_browser	prev_login_time	prev_login_ip	prev_login_browser
				last_error_time	last_error_ip	last_error_browser	monitoring_token	desc
	*/
	public function renderList(): void
	{
		$this->template->users = $this->user_main->getUsers();
	}

	public function actionShow(int $id)
	{
		$this->user_show = $this->user_main->getUser($id);
		if ($this->user_show == null) {
			$this->setView("notFound");
			$this->user_show = $id;
		}
	}

	public function renderShow(): void
	{
		$this->template->userData = $this->user_show;
		$this->template->devices = $this->devices->getDevicesUser($this->user_show->id);
	}

	/*public function renderCreate() {
	}*/

	public function actionEdit(int $id): void
	{
		$this->template->id = $id;

		$this->user_show = $this->user_main->getUser($id);
		if ($this->user_show == null) {
			$this->setView("notFound");
			$this->user_show = $id;
		}
		$this->template->name = $this->user_show->username;

		$this['userForm']->setDefaults($this->user_show);
	}

	/**
	 * Edit user form component factory. Tovarnicka na formular pre editaciu užívateľa.
	 */
	protected function createComponentUserForm(): Form
	{
		$form = $this->editUserForm->create($this->getHttpRequest()->getRemoteAddress());
		$form->onSuccess[] = function ($form) {
			$id = $form->getValues()->id;
			$this->flashOut(!count($form->errors), $id ? ['User:show', $id] : "User:list", 'Údaje boli uložené!', 'Došlo k chybe a údaje sa neuložili. Skúste neskôr znovu...');
		};
		return $this->makeBootstrap4($form);
	}

	public function actionDelete(int $id): void
	{
		$this->template->id = $id;

		$this->user_show = $this->user_main->getUser($id);
		if ($this->user_show == null) {
			$this->setView("notFound");
			$this->user_show = $id;
		}

		$this->template->userData = $this->user_show;
		$this->template->devices = $this->devices->getDevicesUser($id);
	}

	protected function createComponentDeleteForm(): Form
	{
		$form = new Form;
		$form->addProtection();

		$form->addCheckbox('potvrdit', 'Potvrdit smazání')
			->setOption('description', 'Zaškrtnutím potvrďte, že skutečně chcete smazat uživatele a všechna jeho zařízení, data a grafy.')
			->setRequired("Pre zmazanie užívateľa musí byť políčko potvrdenia zmazania zaškrtnuté! ");

		$form->addSubmit('delete', 'Smazat')
			->setHtmlAttribute('class', 'btn btn-danger')
			->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

		$form->onSuccess[] = [$this, 'deleteFormSucceeded'];

		$this->makeBootstrap4($form);
		return $form;
	}

	/** @todo zmeň datasource na user_main */
	public function deleteFormSucceeded(Form $form, array $values): void
	{
		$id = $this->getParameter('id');

		if ($id) {
			// overeni prav
			$this->user_show = $this->user_main->getUser($id);
			if ($this->user_show == null) {
				$this->setView("notFound");
				$this->user_show = $id;
			}
			Logger::log('audit', Logger::INFO, "[{$this->getHttpRequest()->getRemoteAddress()}, {$this->getUser()->getIdentity()->username}] Mazu uzivatele {$id}");

			$this->datasource->deleteViewsForUser($id);
			$devices = $this->devices->getDevicesUser($id);
			foreach ($devices->devices as $device) {

				$this->devices->deleteDevice($device->attrs['id']);
			}
			$this->datasource->deleteUser($id);
		}

		$this->flashMessage("Uživatel smazán.", 'success');
		$this->redirect('User:list');
	}
}
