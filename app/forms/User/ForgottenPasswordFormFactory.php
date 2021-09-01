<?php

namespace App\Forms\User;

use App\Model;
use Language_support;
use Nette\Application\UI\Form;
use Nette\Security;

/**
 * Formular pre vlozenie emailu v pripade zabudnuteho hesla
 * Posledna zmena 03.09.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class ForgottenPasswordFormFactory {
  /** @var Language_support\LanguageMain */
  private $texts;
  /** @var Model\PV_User */
  public $user_main;
  /** @var Nette\Security\User */
  public $user;

  /** @param Security\User $user   */
  public function __construct(Security\User $user, Language_support\LanguageMain $language_main, Model\PV_User $user_main) {
    $this->user = $user;
    $this->texts = $language_main;
    $this->user_main = $user_main;
	}

  /** @return Form */
  public function create(string $language)  {
    $this->texts->setLanguage($language);
    $form = new Form();
		$form->addProtection();
    $form->setRenderer(new UserFormRenderer);
    $form->setTranslator($this->texts);
    $form->addEmail('email', 'SignInForm_email')
          ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 50)
          ->setHtmlAttribute('placeholder', 'SignInForm_email_ph')
          ->addRule(Form::EMAIL, 'SignInForm_email_ar')
          ->setRequired('SignInForm_email_sr');
		$form->addSubmit('send', 'ForgottenPasswordForm_send')
          ->setHtmlAttribute('class', 'btn btn-success btn-block');
    $form->onValidate[] = [$this, 'validateForm'];
		return $form;
	}
  
  /** 
   * Vlastná validácia pre formular */
  public function validateForm(Form $form): void {
    $values = $form->getValues();
    // Over, ci dany email existuje.
    if ( !$this->user_main->testEmail($values->email) ) {
      $form->addError(sprintf($this->texts->translate('ForgottenPasswordForm_user_not_found'), $values->email));
    }
  }
}