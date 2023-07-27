<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Nette\Http\Url;

/**
 * 
 * Posledna zmena 09.06.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2022 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
final class InventoryPresenter extends BasePresenter
{
	use Nette\SmartObject;

	/** @persistent */
	public $viewid = "";

	/** @var Nette\Security\Passwords */
	private $passwords;

	// Database tables	
	/** @var Model\PV_Lang @inject */
	public $lang;

	public function __construct($parameters)
	{
		$this->links = $parameters['links'];
		$this->appName = $parameters['title'];
	}

	public function injectPasswords(Nette\Security\Passwords $passwords)
	{
		$this->passwords = $passwords;
	}

	// ./inventory/user/
	public function renderUser()
	{
		$this->template->userData = $this->user_main->getUser($this->getUser()->id);

		if ($this->template->userData->prev_login_ip != NULL) {
			$this->template->prev_login_name = gethostbyaddr($this->template->userData->prev_login_ip);
			if ($this->template->prev_login_name === $this->template->userData->prev_login_ip) {
				$this->template->prev_login_name = NULL;
			}
		}
		if ($this->template->userData->last_error_ip != NULL) {
			$this->template->last_error_name = gethostbyaddr($this->template->userData->last_error_ip);
			if ($this->template->last_error_name === $this->template->userData->last_error_ip) {
				$this->template->last_error_name = NULL;
			}
		}

		$url = new Url($this->getHttpRequest()->getUrl()->getBaseUrl());
		$this->template->monitoringUrl = $url->getAbsoluteUrl() . "monitor/show/{$this->template->userData['monitoring_token']}/{$this->getUser()->id}/";
	}

	public function actionEdit(): void
	{
		$post = $this->user_main->getUser($this->getUser()->id);
		if (!$post) {
			$this->error($this->texty_presentera->translate('AuthenticationException_1'));
		}
		$this['userForm']->setDefaults($post);
	}

	protected function createComponentUserForm(): Form
	{
		$form = new Form;
		$form->addProtection();

		$form->addEmail('email', 'RegisterForm_email')
			->setRequired('RegisterForm_email_sr')
			->setOption('description', 'RegisterForm_email_sr2')
			->setHtmlAttribute('size', 50);

		$form->addText('monitoring_token', 'inv_monitoring_token_form')
			->setOption('description', 'inv_monitoring_token_form_de')
			->setHtmlAttribute('size', 50);

		$form->addSelect('id_lang', 'inv_userlang', $this->lang->langsForForm());

		$form->addSubmit('send', 'base_save')
			->setHtmlAttribute('class', 'btn btn-success')
			->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

		$form->onSuccess[] = [$this, 'userFormSucceeded'];

		return $this->makeBootstrap4($form);
	}


	public function userFormSucceeded(Form $form, array $values): void
	{
		$this->user_main->updateUser($this->getUser()->id, $values);
		if ($this->language != ($_ac = $this->lang->find($values['id_lang'])->acronym)) {
			$this->language = $_ac;
			$this->texty_presentera->setLanguage($this->language);
		}
		$this->flashRedirect("Inventory:user", $this->texty_presentera->translate('base_save_ok'), "success");
	}


	public function actionPassword(): void
	{
	}

	protected function createComponentPasswordForm(): Form
	{
		$form = new Form;
		$form->addProtection();

		$form->addPassword('oldPassword', 'PasswordChangeForm_heslo')
			->setOption('description', 'PasswordChangeForm_heslo_de')
			->setRequired('PasswordChangeForm_heslo_sr');

		$form->addPassword('password', 'PasswordChangeForm_new_heslo')
			->addRule($form::MIN_LENGTH, 'PasswordChangeForm_new_heslo_ar', 5)
			->setOption('description', 'Nové heslo, které chcete nastavit.')
			->setRequired('PasswordChangeForm_new_heslo_sr');

		$form->addPassword('password2', 'PasswordChangeForm_new_heslo2')
			->setOption('description', 'PasswordChangeForm_new_heslo2_de')
			->setRequired('PasswordChangeForm_new_heslo2_sr')
			->addRule($form::EQUAL, 'PasswordChangeForm_new_heslo2_ar', $form['password'])
			->setOmitted(); // https://doc.nette.org/cs/3.1/form-presenter#toc-validacni-pravidla

		$form->addSubmit('send', 'PasswordChangeForm_new_send')
			->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

		$form->onSuccess[] = [$this, 'passwordFormSucceeded'];

		return $this->makeBootstrap4($form);
	}


	public function passwordFormSucceeded(Form $form, array $values): void
	{

		$post = $this->user_main->getUser($this->getUser()->id);
		if (!$post) {
			$this->error($this->texty_presentera->translate('AuthenticationException_1'));
		}
		if (!$this->passwords->verify($values['oldPassword'], $post->phash)) {
			$form->addError($this->texty_presentera->translate('pass_old_error'));
			return;
		}

		$hash = $this->passwords->hash($values['password']);
		$this->user_main->updateUserPassword($this->getUser()->id, $hash);

		$this->flashRedirect("Inventory:user", $this->texty_presentera->translate('pass_change_ok'), "success");
	}
}
