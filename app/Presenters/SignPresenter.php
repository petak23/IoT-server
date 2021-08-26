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

  public $links;

  /** @var Model\PV_User @inject */
	public $pv_user;

  /** @var Services\MailService @inject*/
  private $mailService;

  // -- Forms
  /** @var User\RegisterFormFactory @inject*/
	public $registerForm;
  ///** @var User\ResetPasswordFormFactory @inject*/
	//public $resetPasswordForm;
  /** @var User\ForgottenPasswordFormFactory @inject*/
	public $forgottenPasswordForm;
  /** @var User\SignInFormFactory @inject*/
	public $signInForm;

  public function __construct(Services\Config $config )
  {
    $this->links = $config->links;
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
    
	protected function createComponentSignInForm(): Form {
    $form = $this->signInForm->create($this->language, $this->email);
		$form->onSuccess[] = function ($form) { 
			// https://pla.nette.org/cs/jak-po-odeslani-formulare-zobrazit-stejnou-stranku
      $this->restoreRequest($this->backlink);

      $this->redirect('Inventory:user');
			$this->flashOut(!count($form->errors), $id ? ['Inventory:user'] : "Sign:in", 'Vitajte!', 'Došlo k chybe pri prihlásení. Skúste neskôr znovu...');
		};

    return $form;
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
    $form['send']->onClick[] = [$this, 'forgotPasswordFormSubmitted'];
		return $form;
	}

  /** 
   * Spracovanie formulara zabudnuteho hesla
   * @param Nette\Application\UI\Form $button Data formulara */
  public function forgotPasswordFormSubmitted($button) {
		//Inicializacia
    $values = $button->getForm()->getValues();                 //Nacitanie hodnot formulara
    $clen = $this->pv_user->getUserBy(['email'=>$values->email]);
    $tp = $this->texty_presentera;
    $new_password_requested = StrFTime("%Y-%m-%d %H:%M:%S", Time());
    $new_password_key = Nette\Utils\Random::generate(25);
    if (isset($clen->email) && $clen->email == $values->email) { //Taky clen existuje
      $templ = new Latte\Engine;
      $params = [
        "site_name"             => $this->nazov_stranky,
        "nadpis"                => sprintf($tp->translate('email_reset_nadpis'),$this->nazov_stranky),
        "email_reset_txt"       => $tp->translate('email_reset_txt'),
        "email_nefunkcny_odkaz" => $tp->translate('email_nefunkcny_odkaz'),
        "email_pozdrav"         => $tp->translate('email_pozdrav'),
        "nazov"                 => $tp->translate('forgot_pass'),
        "odkaz" 		            => 'http://'.$this->nazov_stranky.$this->link("User:resetPassword", $clen->id, $new_password_key),
      ];
      /*$mail = new Message;
      $mail->setFrom($this->nazov_stranky.' <'.$this->clen->email.'>')
            ->addTo($values->email)->setSubject($tp->translate('forgot_pass'))
            ->setHtmlBody($templ->renderToString(__DIR__ . '/../templates/User/forgot_password-html.latte', $params));
      try {
        $sendmail = new SendmailMailer;
        $sendmail->send($mail);
        $this->user_main->find($clen->id)->update(['new_password_key'=>$new_password_key, 'new_password_requested'=>$new_password_requested]);
        $this->flashMessage($tp->translate('forgot_pass_email_ok'), 'success');
      } catch (Exception $e) {
        $this->flashMessage($tp->translate('send_email_err').$e->getMessage(), 'danger,n');
      }*/

      $this->mailService->sendMail( 
        $values->email,
        $tp->translate('forgot_pass'),
        $templ->renderToString(__DIR__ . '/templates/User/forgot_password-html.latte', $params)
      );

      $this->flashRedirect('Sign:in', $tp->translate('forgot_pass_email_ok'), 'success');
    } else {													//Taky uzivatel neexzistuje
      $this->flashMessage(sprintf($tp->translate('forgot_pass_user_err'),$values->email), 'danger');
    }
  }
}
