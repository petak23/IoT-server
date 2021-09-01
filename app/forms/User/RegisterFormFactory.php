<?php

namespace App\Forms\User;

use App\Model;
use Language_support;
use Nette\Application\UI\Form;
use Nette\Security\User;

/**
 * Registracny formular
 * Posledna zmena 06.09.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
class RegisterFormFactory {
  /** @var Security\User */
  protected $user;
  /** @var Language_support\LanguageMain */
  private $texts;
  /** @var Model\PV_User */
  private $pv_user;
  /** @var string */
  private $link_forgot;  

  /**
   * @param User $user
   * @param Language_support\LanguageMain $language_main
   * @param DbTable\User_main $user_main */
  public function __construct(User $user, Language_support\LanguageMain $language_main, Model\PV_User $pv_user) {
    $this->user = $user;
    $this->texts = $language_main;
    $this->pv_user = $pv_user;
	}
  
  /** Formular */
  public function create(string $link_forgot, string $language): Form   {
    $this->link_forgot = $link_forgot;
    $this->texts->setLanguage($language);

    $form = new Form();
    $form->setRenderer(new UserFormRenderer);
		$form->addProtection();
    $form->setTranslator($this->texts);
    $form->addEmail('email', 'RegisterForm_email')
          ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
          ->addRule(Form::EMAIL, 'RegisterForm_email_ar')
          ->setRequired('RegisterForm_email_sr');

    $form->addPassword('password', 'RegisterForm_psswd')
          ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
          ->addRule(Form::MIN_LENGTH, 'RegisterForm_psswd_ar', 5)
          ->setRequired('RegisterForm_psswd_sr');

    $form->addPassword('password2', 'RegisterForm_psswd2')
          ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
          ->addRule(Form::EQUAL, 'RegisterForm_psswd2_ar', $form['password'])
          ->setRequired('RegisterForm_psswd2_sr')
          ->setOmitted(); // https://doc.nette.org/cs/3.1/form-presenter#toc-validacni-pravidla

    $form->addSubmit('send', 'RegisterForm_send')
        ->setHtmlAttribute('class', 'btn btn-success');

    $form->onValidate[] = [$this, 'validateRegisterForm'];
		return $form;
	}
  
  /** 
   * Vlastná validácia pre RegisterForm */
  public function validateRegisterForm(Form $form): void {
    $values = $form->getValues();
    // Over, ci dany email uz existuje.
    if ($this->pv_user->testEmail($values->email)) {
      $form->addError(sprintf($this->texts->translate('RegisterForm_email_duble2'), $values->email, $this->link_forgot));
    }
  }
  
}