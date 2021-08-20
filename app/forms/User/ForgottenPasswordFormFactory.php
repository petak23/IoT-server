<?php

namespace App\Forms\User;

use App\Model;
use Language_support;
use Nette\Application\UI\Form;
use Nette\Security;

/**
 * Formular pre vlozenie emailu v pripade zabudnuteho hesla
 * Posledna zmena 28.07.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class ForgottenPasswordFormFactory {
  /** @var Language_support\LanguageMain */
  private $texts;
  /** @var Model\PV_User */
  public $pv_user;
  /** @var Nette\Security\User */
  public $user;

  /** @param Security\User $user   */
  public function __construct(Security\User $user, Language_support\LanguageMain $language_main, Model\PV_User $pv_user) {
    $this->user = $user;
    $this->texts = $language_main;
    $this->pv_user = $pv_user;
	}

  /** @return Form */
  public function create(string $language)  {
    $this->texts->setLanguage($language);
    $form = new Form();
		$form->addProtection();
    $form->setTranslator($this->texts);
    $form->addEmail('email', 'Form_email')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 50)
         ->setHtmlAttribute('placeholder', 'Form_email_ph')
				 ->addRule(Form::EMAIL, 'Form_email_ar')
				 ->setRequired('Form_email_sr');
		$form->addSubmit('uloz', 'ForgottenPasswordForm_uloz')
         ->setHtmlAttribute('class', 'btn btn-success btn-block');
    $form->onValidate[] = [$this, 'validateForm'];
		return $form;
	}
  
  /** Vlastná validácia pre formular
   * @param Nette\Application\UI\Form $button */
  public function validateForm($button) {
    $values = $button->getForm()->getValues();
    if ($button->isSubmitted()->name == 'uloz') {
      // Over, ci dany email existuje.
      if ( !$this->pv_user->testEmail($values->email) ) {
        $button->addError(sprintf($this->texts->translate('forgot_pass_user_err'), $values->email));
      }
    } 
  }
}