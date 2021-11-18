<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\User;
use App\Model;
use App\Services;
use App\Services\Logger;
use Latte;
use Nette;
use Nette\Application\UI\Form;
use PeterVojtech;


/**
 * Sign in form
 * Last change 01.09.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 * 
 * @todo https://getbootstrap.com/docs/5.1/components/navs-tabs/#javascript-behavior
 */
class SignPresenter extends MainBasePresenter {
  
  public $email = '';

  /** @persistent */
	public $backlink = '';

  /** @var Model\PV_User @inject */
	public $user_main;

  /** @var Services\MailService */
  private $mailService;

  protected $my_params;

  // -- Forms
  /** @var User\RegisterFormFactory @inject*/
	public $registerForm;
  /** @var User\ResetPasswordFormFactory @inject*/
	public $resetPasswordForm;
  /** @var User\ForgottenPasswordFormFactory @inject*/
	public $forgottenPasswordForm;

  use PeterVojtech\User\Sign\signInTrait;

  public function __construct($parameters,
                              Services\Config $config,
                              Services\MailService $mailService
                              ) {
    $this->links = $config->links;  // Definet in MainBasePresenter
    $this->my_params = $parameters;
    $this->mailService = $mailService;
  }

  /** 
   * Akcia pre prihlásenie
   * @var string $email pre prípad prednastavenia */
  public function actionIn( $email = NULL ): void {
    $response = $this->getHttpResponse();
    $response->setHeader('Cache-Control', 'no-cache');
    $response->setExpiration('1 sec'); 

    $this->email = $email;
  }

  public function beforeRender(): void {
    parent::beforeRender();
    $this->template->links = $this->links;
  }
    
  public function actionOut(): void {
    $response = $this->getHttpResponse();
    $response->setHeader('Cache-Control', 'no-cache');
    $response->setExpiration('1 sec'); 

    if( $this->getUser()->getIdentity() ) {
      Logger::log( 'audit', Logger::INFO , 
          "[{$this->getHttpRequest()->getRemoteAddress()}] Logout: odhlasen {$this->getUser()->getIdentity()->email}" ); 

    }
    $this->getUser()->logout(true); // Vymaže aj identitu
    $this->flashRedirect('Sign:in', $this->texty_presentera->translate("base_log_out_mess"), "success");
  }

  public function renderForgottenPassword(): void {
    
  }

  /**
	 * Forgot password user form component factory.
	 * @return Nette\Application\UI\Form */
	protected function createComponentForgottenPasswordForm() {
    $form = $this->forgottenPasswordForm->create($this->language);
    $form['send']->onClick[] = [$this, 'forgotPasswordFormSucceeded'];
		return $form;
	}

  /** 
   * Spracovanie formulara zabudnuteho hesla */
  public function forgotPasswordFormSucceeded(Form $form, \stdClass $values): void {
    $fpuser = $this->user_main->getUserBy(['email'=>$values->email]);
    $tp = $this->texty_presentera; // Pre skrátenie
    $new_password_requested = StrFTime("%Y-%m-%d %H:%M:%S", Time());
    $new_password_key = Nette\Utils\Random::generate(25);
    if (isset($fpuser->email) && $fpuser->email == $values->email) { //Uzivatel existuje
      $templ = new Latte\Engine;
      $params = [
        "site_name"  => $this->site_name,
        "title"      => sprintf($tp->translate('email_reset_title'), $this->site_name),
        "first_txt"  => $tp->translate('email_reset_txt'),
        "second_txt" => $tp->translate('email_nefunkcny_odkaz'),
        "greeting"   => $tp->translate('email_pozdrav'),
        "link" 		   => $this->link("//Sign:resetPassword", $fpuser->id, $new_password_key),
      ];
      try {
        $this->mailService->sendMail2( 
          $values->email,
          __DIR__ . '/../templates/Sign/forgot_password-html.latte', 
          $params );
        $this->user_main->save($fpuser->id, [
          'new_password_key' => $new_password_key,
          'new_password_requested' => $new_password_requested,
        ]);
        $this->flashRedirect('Sign:in', $tp->translate('ForgottenPasswordForm_email_ok'), 'success');
      } catch (Services\SendException $e) {
        $this->flashMessage(sprintf($tp->translate('ForgottenPasswordForm_email_err'), $e->getMessage()), 'danger');  
      }
    } else {													//Uzivatel neexzistuje
      $this->flashMessage(sprintf($tp->translate('ForgottenPasswordForm_email_err1'),$values->email), 'danger');
    }
  }

  /** 
   * Akcia pre reset hesla pri zabudnutom hesle 
   * @param int $id Id uzivatela pre reset hesla
   * @param string $new_password_key Kontrolny retazec pre reset hesla */
  public function actionResetPassword(int $id, string $new_password_key): void {
    
    if (!isset($id) OR !isset($new_password_key)) {
      $this->flashRedirect('Sign:in', $this->texty_presentera->translate('reset_pass_err1'), 'danger');
    } else {
      $user_main_data = $this->user_main->getUser($id);
      //dumpe($new_password_key);
      if ($new_password_key == $user_main_data->new_password_key){ 
        $this->template->email = sprintf($this->texty_presentera->translate('reset_pass_email'), $user_main_data->email);
        $this["resetPasswordForm"]->setDefaults(["id"=>$id]); //Nastav vychodzie hodnoty
      } else { 
        $this->flashRedirect('Sign:in', $this->texty_presentera->translate('reset_pass_err'.($user_main_data->new_password_key == NULL ? '2' : '3')), 'danger');
      }
    }
  }

   /**
	 * Reset password user form component factory.
	 * @return Nette\Application\UI\Form */
	protected function createComponentResetPasswordForm() {
    $form = $this->resetPasswordForm->create($this->language);
    $form->onSuccess[] = function(Form $form) {
      $this->flashRedirect('Sign:in', $this->texty_presentera->translate('pass_change_ok'), 'success');
    };
        
		return $form;
	}
}
