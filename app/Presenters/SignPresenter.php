<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\User;
use App\Services;
use App\Services\Logger;
use Latte;
use Nette;
use Nette\Application\UI\Form;


/**
 * @last_edited petak23<petak23@gmail.com> 28.07.2021
 */
class SignPresenter extends MainBasePresenter
{
  /** @persistent */
  public $username = '';

  /** @persistent */
	public $backlink = '';

  public $links;

  /** @var Services\MailService @inject*/
  private $mailService;

  // -- Forms
  /** @var User\RegisterFormFactory @inject*/
	public $registerForm;
  ///** @var User\ResetPasswordFormFactory @inject*/
	//public $resetPasswordForm;
  /** @var User\ForgottenPasswordFormFactory @inject*/
	public $forgottenPasswordForm;

  public function __construct(Services\Config $config )
  {
    $this->links = $config->links;
  }

  public function actionIn( $username=NULL ): void
  {
    $response = $this->getHttpResponse();
    $response->setHeader('Cache-Control', 'no-cache');
    $response->setExpiration('1 sec'); 

    $this->username = $username;
  }

  public function beforeRender(): void {
    parent::beforeRender();
    $this->template->links = $this->links;
  }
    
	protected function createComponentSignInForm(): Form
	{
		$form = new Form;
		$form->addText('email', 'Uživatelský email:')
          ->setRequired('Prosím vyplňte svuj uživatelský email.')
          ->setDefaultValue( $this->username );

		$form->addPassword('password', 'Heslo:')
			    ->setRequired('Prosím vyplňte své heslo.');

    $form->addSubmit('send', 'Přihlásit')
        ->setHtmlAttribute('class', 'btn btn-success btn-block')
        ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        
		$form =  $this->makeBootstrap4( $form );
    $renderer = $form->getRenderer();
    $renderer->wrappers['control']['container'] = 'div class=col-sm-8';
    $renderer->wrappers['label']['container'] = 'div class="col-sm-4 col-form-label align-top text-right"';
    
    return $form;
  }

  public function signInFormSucceeded(Form $form, \stdClass $values): void
  {
    try {
      $this->getUser()->setExpiration('30 hour');
      $this->getUser()->login($values->email, $values->password);

      // https://pla.nette.org/cs/jak-po-odeslani-formulare-zobrazit-stejnou-stranku
      $this->restoreRequest($this->backlink);

      $this->redirect('Inventory:user');

    } catch (Nette\Security\AuthenticationException $e) {
      $form->addError("Přihlášení se nepodařilo: {$e->getMessage()}");
    } catch ( \App\Exceptions\UserNotEnrolledException $e ) {
      $this->flashRedirect(["Enroll:step2", $values->email], "Nejprve aktivujte účet zadáním kódu z e-mailu.", "warning");
    }
  }

  public function actionOut(): void
  {
    $response = $this->getHttpResponse();
    $response->setHeader('Cache-Control', 'no-cache');
    $response->setExpiration('1 sec'); 

    if( $this->getUser()->getIdentity() ) {
      Logger::log( 'audit', Logger::INFO , 
          "[{$this->getHttpRequest()->getRemoteAddress()}] Logout: odhlasen {$this->getUser()->getIdentity()->username}" ); 

    }
    $this->getUser()->logout(true); // Vymaže aj identitu
    $this->flashRedirect('Sign:in', 'Odhlášení bylo úspěšné.', "success");
  }

  public function renderForgottenPassword(): void {
    
  }

    /**
	 * Forgot password user form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentForgottenPasswordForm() {
    $form = $this->forgottenPasswordForm->create($this->language);
    $form['uloz']->onClick[] = [$this, 'forgotPasswordFormSubmitted'];

    $form =  $this->makeBootstrap4( $form );
    $renderer = $form->getRenderer();
    $renderer->wrappers['control']['container'] = 'div class=col-sm-8';
    $renderer->wrappers['label']['container'] = 'div class="col-sm-4 col-form-label align-top text-right"';
		return $form;
	}

  /** 
   * Spracovanie formulara zabudnuteho hesla
   * @param Nette\Application\UI\Form $button Data formulara */
  public function forgotPasswordFormSubmitted($button) {
		//Inicializacia
    $values = $button->getForm()->getValues();                 //Nacitanie hodnot formulara
    $clen = $this->user_main->findOneBy(['email'=>$values->email]);
    
    $new_password_requested = StrFTime("%Y-%m-%d %H:%M:%S", Time());
    $new_password_key = Nette\Utils\Random::generate(25);
    if (isset($clen->email) && $clen->email == $values->email) { //Taky clen existuje
      $templ = new Latte\Engine;
      $params = [
        "site_name" => $this->nazov_stranky,
        "nadpis"    => sprintf($this->texty_presentera->translate('email_reset_nadpis'),$this->nazov_stranky),
        "email_reset_txt" => $this->texty_presentera->translate('email_reset_txt'),
        "email_nefunkcny_odkaz" => $this->texty_presentera->translate('email_nefunkcny_odkaz'),
        "email_pozdrav" => $this->texty_presentera->translate('email_pozdrav'),
        "nazov"     => $this->texty_presentera->translate('forgot_pass'),
        "odkaz" 		=> 'http://'.$this->nazov_stranky.$this->link("User:resetPassword", $clen->id, $new_password_key),
      ];
      /*$mail = new Message;
      $mail->setFrom($this->nazov_stranky.' <'.$this->clen->email.'>')
           ->addTo($values->email)->setSubject($this->texty_presentera->translate('forgot_pass'))
           ->setHtmlBody($templ->renderToString(__DIR__ . '/../templates/User/forgot_password-html.latte', $params));
      try {
        $sendmail = new SendmailMailer;
        $sendmail->send($mail);
        $this->user_main->find($clen->id)->update(['new_password_key'=>$new_password_key, 'new_password_requested'=>$new_password_requested]);
        $this->flashMessage($this->texty_presentera->translate('forgot_pass_email_ok'), 'success');
      } catch (Exception $e) {
        $this->flashMessage($this->texty_presentera->translate('send_email_err').$e->getMessage(), 'danger,n');
      }*/

      $this->mailService->sendMail( 
        $values->email,
        $this->texty_presentera->translate('forgot_pass'),
        $templ->renderToString(__DIR__ . '/templates/User/forgot_password-html.latte', $params)
      );

      $this->flashRedirect('Sign:in', $this->texty_presentera->translate('forgot_pass_email_ok'), 'success');
    } else {													//Taky uzivatel neexzistuje
      $this->flashMessage(sprintf($this->texty_presentera->translate('forgot_pass_user_err'),$values->email), 'danger');
    }
  }
}
