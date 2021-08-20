<?php

namespace App\Forms\User;

use App\Model;
use Language_support;
use Nette\Application\UI\Form;
use Nette\Security;

/**
 * Formular pre reset hesla
 * Posledna zmena 28.07.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.8
 */
class ResetPasswordFormFactory {
  /** @var Security\User */
  private $user;
  /** @var Language_support\LanguageMain */
  private $texts;
  /** @var Model\PV_User */
  private $pv_user;
  /** @var Security\Passwords */
  private $passwords;

  /** @param Security\User $user   */
  public function __construct(Security\User $user,
                              Security\Passwords $passwords,
                              Language_support\LanguageMain $language_main, 
                              Model\PV_User $pv_user) {
    $this->user = $user;
    $this->texts = $language_main;
    $this->pv_user = $pv_user;
    $this->passwords = $passwords;
	}
  
  /**
   * Prihlasovaci formular
   * @return Nette\Application\UI\Form */
  public function create(string $language)  {
    $this->texts->setLanguage($language);
    $form = new Form();
		$form->addProtection();
    $form->setTranslator($this->texts);
//    $form->addProtection();
    $form->addHidden('id');
    $form->addPassword('new_heslo', 'ResetPasswordForm_new_heslo')
         ->setHtmlAttribute('autofocus', 'autofocus')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
				 ->setRequired('ResetPasswordForm_new_heslo_sr');
		$form->addPassword('new_heslo2', 'ResetPasswordForm_new_heslo2')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
         ->addRule(Form::EQUAL, 'ResetPasswordForm_new_heslo2_ar', $form['new_heslo'])
				 ->setRequired('ResetPasswordForm_new_heslo2_sr')
         ->setOmitted();
		$form->addSubmit('uloz', 'base_save');
    $form->onValidate[] = [$this, 'validateResetForm'];
    $form->onSuccess[] = [$this, 'userPasswordResetFormSubmitted'];
		return $form;
	}
  
  /** Vlastná validácia
   * @param Nette\Application\UI\Form $button */
  public function validateResetForm($button) {
    $values = $button->getForm()->getValues();
    if ($button->isSubmitted()->name == 'uloz') {
      if ($values->new_heslo != $values->new_heslo2) {
        $button->addError($this->texts->translate('reset_pass_hesla_err'));
      }
    } 
  }
  
  /** 
   * Overenie po odoslani
   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */
  public function userPasswordResetFormSubmitted($button) {
    $values = $button->getForm()->getValues(); //Nacitanie hodnot formulara
		if ($values->new_heslo != $values->new_heslo2) {
      $button->addError($this->texts->translate('reset_pass_hesla_err'));
		} else {
      //Vygeneruj kluc pre zmenu hesla
      $new_password = $this->passwords->hash($values->new_heslo);
      unset($values->new_heslo, $values->new_heslo2); //Len pre istotu
      //try {
        $this->pv_user->save($values->id, ['password'=>$new_password, 'new_password_key'=>NULL, 'new_password_requested'=>NULL]);
      /*} catch (Exception $e) {
        $button->addError($e->getMessage());
      }*/
    }
	}
}
