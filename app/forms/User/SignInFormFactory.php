<?php

declare(strict_types=1);

namespace App\Forms\User;

use App\Exceptions;
use Language_support;
use Nette\Application\UI\Form;
use Nette\Security;

/**
 * Sign in form
 * Last change 24.08.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.1.2
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
    $form->addProtection();
    $this->texts->setLanguage($language);
    $form->setTranslator($this->texts);
		$em = $form->addEmail('email', 'SignInForm_email')
                ->setHtmlAttribute('autofocus', 'autofocus')
                ->setRequired('SignInForm_email_req');
    if ($email !== null) $em->setDefaultValue($email);

		$form->addPassword('password', 'SignInForm_password')
          ->addRule(Form::MIN_LENGTH, 'SignInForm_password_min_lenght', 3)
          ->setRequired('SignInForm_password_req');
    
    $form->addCheckbox('remember', 'SignInForm_remember');
    
    $form->addSubmit('send', 'SignInForm_login')
        ->setHtmlAttribute('class', 'btn btn-success btn-block')
        ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = null;
    $renderer->wrappers['control']['container'] = 'div class=col-sm-8';
    $renderer->wrappers['label']['container'] = 'div class="col-sm-4 col-form-label align-top text-right"';
    $renderer->wrappers['pair']['container'] = 'div class="form-group row"';
    $renderer->wrappers['pair']['.error'] = 'has-danger';
    
    $renderer->wrappers['control']['description'] = 'span class="form-text font-italic"';
    $renderer->wrappers['control']['errorcontainer'] = 'span class=form-control-feedback';
    $renderer->wrappers['control']['.error'] = 'is-invalid';

    $renderer->wrappers['error']['container'] = 'ul class="error alert alert-danger text-center p-2"';
    $renderer->wrappers['error']['item'] = 'li'; //has-danger


    foreach ($form->getControls() as $control) {
      $type = $control->getOption('type');
      if ($type === 'button' && !(isset($control->getControlPrototype()->attrs['class']) && strlen($control->getControlPrototype()->attrs['class']))) {
        $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-secondary');
        $usedPrimary = true;
      } elseif ($type == 'text') {
        $control->getControlPrototype()->addClass('form-control');
      } elseif (in_array($type, ['checkbox', 'radio'], true)) {
        if ($control instanceof \Nette\Forms\Controls\Checkbox) {
          $control->getLabelPrototype()->addClass('form-check-label');
        } else {
          $control->getItemLabelPrototype()->addClass('form-check-label');
        }
        $control->getControlPrototype()->addClass('form-check-input');
        $control->getSeparatorPrototype()->setName('div')->addClass('form-check');
      }
    }
    return $form;
  }
  
  public function signInFormSucceeded(Form $form, \stdClass $values): void {
    try {
      $this->user->setExpiration($values->remember ? '14 days' : '30 minutes');
      $this->user->login($values->email, $values->password);
    } catch (Security\AuthenticationException $e) {
      $form->addError(sprintf($this->texts->translate("SignInForm_main_error"), sprintf($this->texts->translate("AuthenticationException_".$e->getCode()), $e->getMessage())));
    } catch ( Exceptions\UserNotEnrolledException $e ) {
      $this->addError($this->texts->translate("UserNotEnrolledException"));
    }
  }
}
