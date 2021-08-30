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
 * Last change 26.08.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class SignPresenter extends MainBasePresenter {
  
  public $email = '';

  /** @persistent */
	public $backlink = '';

  /** @var Model\PV_User @inject */
	public $pv_user;

  /** @var Services\MailService @inject*/
  private $mailService;

  protected $my_params;

  // -- Forms
  /** @var User\RegisterFormFactory @inject*/
	public $registerForm;
  ///** @var User\ResetPasswordFormFactory @inject*/
	//public $resetPasswordForm;
  /** @var User\ForgottenPasswordFormFactory @inject*/
	public $forgottenPasswordForm;
  /** @var User\SignInFormFactory @inject*/
	//public $signInForm;

  use PeterVojtech\User\signInTrait;

  public function __construct($parameters, Services\Config $config ) {
    $this->links = $config->links;  // Definet in MainBasePresenter
    $this->my_params = $parameters;
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
    $fpuser = $this->pv_user->getUserBy(['email'=>$values->email]);
    $tp = $this->texty_presentera; // Pre skrátenie
    $new_password_requested = StrFTime("%Y-%m-%d %H:%M:%S", Time());
    $new_password_key = Nette\Utils\Random::generate(25);
    if (isset($fpuser->email) && $fpuser->email == $values->email) { //Uzivatel existuje
      $templ = new Latte\Engine;
      $params = [
        //"site_name"             => $this->nazov_stranky,
        "nadpis"                => sprintf($tp->translate('email_reset_nadpis'), 'iot...'),//$this->nazov_stranky),
        "email_reset_txt"       => $tp->translate('email_reset_txt'),
        "email_nefunkcny_odkaz" => $tp->translate('email_nefunkcny_odkaz'),
        "email_pozdrav"         => $tp->translate('email_pozdrav'),
        "nazov"                 => $tp->translate('forgot_pass'),
        "odkaz" 		            => $this->link("//User:resetPassword", $fpuser->id, $new_password_key),
      ];
      try {
        $this->mailService->sendMail( 
          $values->email,
          $tp->translate('forgot_pass'),
          $templ->renderToString(__DIR__ . '/templates/User/forgot_password-html.latte', $params)
        );
        $this->pv_user->save($fpuser->id, [
          'new_password_key' => $new_password_key,
          'new_password_requested' => $new_password_requested,
        ]);
        $this->flashRedirect('Sign:in', $tp->translate('ForgottenPasswordForm_email_ok'), 'success');
      } catch (Exception $e) {
        $this->flashMessage(sprintf($tp->translate('ForgottenPasswordForm_email_err'), $e->getMessage()), 'danger');  
      }
    } else {													//Uzivatel neexzistuje
      $this->flashMessage(sprintf($tp->translate('ForgottenPasswordForm_email_err1'),$values->email), 'danger');
    }
  }
}
