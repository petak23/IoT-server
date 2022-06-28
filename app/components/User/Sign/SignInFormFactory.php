<?php

declare(strict_types=1);

namespace PeterVojtech\User\Sign;

use App\Exceptions;
use App\Forms;
use Language_support;
use Nette\Application\UI\Form;
use Nette\Security;

/**
 * Sign in form
 * Last change 03.09.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.1.6
 */
class SignInFormFactory {
  /** @var User */
  private $user;
  /** @var Language_support\LanguageMain */
  private $texts;

  /**
   * @param Security\User $user
   * @param Language_support\LanguageMain $language_main */
  public function __construct(Security\User $user,
                              Language_support\LanguageMain $language_main) {
    $this->user = $user;
    $this->texts = $language_main;
	}
  
  /**
   * @return string */
  public function getTexts() {
    return $this->texts;
  }

  /**
   * Prihlasovaci formular
   * @var string $language Skratka aktualneho jazyka
   * @var string $email Pre prípad prednastavenia
   * @return Form */
  public function create(string $language, string $email = null): Form {
		$form = new Form;
    $form->setRenderer(new Forms\User\UserFormRenderer); // https://github.com/tomaj/nette-bootstrap-form

    $form->addProtection();
    $this->texts->setLanguage($language);
    $form->setTranslator($this->texts);
		$em = $form->addEmail('email', 'SignInForm_email')
                ->setHtmlAttribute('autofocus', 'autofocus')
                ->addRule(Form::EMAIL, 'SignInForm_email_ar')
                ->setRequired('SignInForm_email_sr');
    if ($email !== null) $em->setDefaultValue($email);

		$form->addPassword('password', 'SignInForm_password')
          ->addRule(Form::MIN_LENGTH, 'SignInForm_password_min_lenght', 3)
          ->setRequired('SignInForm_password_req');
    
    $form->addCheckbox('remember', 'SignInForm_remember');
    
    $form->addSubmit('send', 'SignInForm_login')
        ->setHtmlAttribute('class', 'btn btn-success btn-block')
        ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        
    return $form;
  }
  
  public function signInFormSucceeded(Form $form, \stdClass $values): void {
    try {
      $this->user->setExpiration($values->remember ? '14 days' : '30 minutes');
      $this->user->login($values->email, $values->password);
    } catch ( Security\AuthenticationException $e) {
      $form->addError(sprintf($this->texts->translate("SignInForm_main_error"), sprintf($this->texts->translate("AuthenticationException_".$e->getCode()), $e->getMessage())));
    } catch ( Exceptions\UserNotEnrolledException $e ) {
      $form->addError($this->texts->translate("UserNotEnrolledException"));
    }
  }

}