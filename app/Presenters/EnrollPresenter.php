<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Nette\Mail\Mailer;
use Nette\Mail\Message; 
use Nette\Http\Url;

use App\Exceptions;
use App\Forms;
use App\Model;
use App\Services\Logger;
use App\Services;

/**
 * Registrácia užívateľa
 * Last change 06.09.2021
 * 
 * @github     Forked from petrbrouzda/RatatoskrIoT
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class EnrollPresenter extends MainBasePresenter {

  /** @var Model\PV_User @inject */
	public $pv_user;

  /** @var Passwords */
  private $passwords;

  private $mailService;

  /** @persistent */
  public $email = "";

  public $links;

  // -- Forms
  /** @var Forms\User\RegisterFormFactory @inject*/
	public $enrollForm;
  /** @var Forms\User\Enroll2FormFactory @inject*/
	public $enroll2Form;
  

  public function __construct(Services\MailService $mailsv,
                              Passwords $passwords,
                              Services\Config $config   )
	{
    $this->passwords = $passwords;
    $this->mailService = $mailsv;
    $this->links = $config->links;
  }

  public function beforeRender(): void
  {
    parent::beforeRender();
    $this->template->links = $this->links;
  }

  protected function createComponentEnrollForm(): Form {
		$form = $this->enrollForm->create($this->link("Sign:ForgottenPassword"), $this->language);
		$form->onSuccess[] =  [$this, 'enrollFormSucceeded'];
		return $form;
  }

  public function enrollFormSucceeded(Form $form, Nette\Utils\ArrayHash $values ): void
  {
    $hash = $this->passwords->hash($values->password);
    
    // Vytvorenie prefix-u
    $arr = preg_split( '/[_.@\\-\\+]/', $values->email, 0, PREG_SPLIT_NO_EMPTY );
    $prefixBase = '';
    $prefix = '';
    foreach( $arr as $str ) {
      $prefixBase .= substr( $str, 0, 1 );
      if( strlen($prefixBase)==2 ) break;
    }
    for( $i=0; ; $i++ ) {
      $prefix = $prefixBase . ($i>0 ? $i : '' );
      if( count($this->pv_user->getPrefix( $prefix )) == 0 ) {
        break;
      }
    }
    $prefix = strtolower( $prefix );

    $code = Nette\Utils\Random::generate(4, '0-9');

    try { 
      $this->pv_user->createEnrollUser( $values, $hash, $prefix, $code );
    } catch (Exceptions\UserDuplicateEmailException $e) {
      $this->flashRedirect("Enroll:step1", $this->texty_presentera->translate("RegisterForm_email_duble2"), "warning");
    }

    Logger::log( 'audit', Logger::INFO , 
        "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: zalozeny {$values->email} prefix=[{$prefix}] [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 
    
    $mailUrl = $this->link("//Enroll:step2", ['email'=>$values->email, 'code'=>$code]); // Lomítka na zač. znamená absolútna adresa

    $this->mailService->sendMail( 
        $values->email,
        $this->texty_presentera->translate("Enroll2Form_mail_subject"),
        sprintf($this->texty_presentera->translate("Enroll2Form_mail_inner"), $code, $mailUrl)
    );

    $this->flashRedirect(["Enroll:step2", $values->email], sprintf($this->texty_presentera->translate("Enroll2Form_mail_send"), $values->email), "success");
  }
    
  public function actionStep2( $email, $code = NULL )
  {
    $this->email = $email;

    // pokud jsou vyplneny oba parametry, rovnou na overovani
    if( $code ) {
      $this->validujKod( $email, $code );
    }
    $this->template->email = $email;
  }

  protected function createComponentStep2Form(): Form {
    $form = $this->enroll2Form->create($this->language);
		$form->onSuccess[] =  [$this, 'step2FormSucceeded'];
		return $form;
  }

  public function step2FormSucceeded(Form $form, Nette\Utils\ArrayHash $values ): void {
    $this->validujKod( $this->email, $values->code );
  }

  private function validujKod( $email, $code ) {
    $userdata = $this->pv_user->getUserBy(['email'=>$email]);

    if( !$userdata ) {
      Logger::log( 'audit', Logger::ERROR , 
          "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: nenajdeny $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

      $this->flashRedirect("Enroll:step2", sprintf($this->texty_presentera->translate("Enroll2Form_validate_not_found"), $email), "danger");
    }

    if( $userdata->id_user_state != 1) {
      Logger::log( 'audit', Logger::ERROR , 
          "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: chybny stav {$userdata->id_user_state} pre $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

      $this->flashRedirect(["Sign:in", $email], sprintf($this->texty_presentera->translate("Enroll2Form_validate_ok_code"), $email), "success"); 
    }

    if( $userdata->self_enroll_code !== $code) {

      if( $userdata->self_enroll_error_count >= 3 ) { 
        // smazat ucet
        Logger::log( 'audit', Logger::ERROR , 
            "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: MAZEM UCET, chybny kod pre $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

        $this->pv_user->deleteUserByEmailEnroll( $email );
        $this->flashRedirect("Sign:in", $this->texty_presentera->translate("Enroll2Form_validate_er_delete"), "danger");
      } else {
        // navysit pocet chyb
        Logger::log( 'audit', Logger::ERROR , 
            "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: chybny kod pre $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

        $this->pv_user->updateUserEnrollState( $email, 1, 1 + $userdata->self_enroll_error_count, 2 );
        $this->flashRedirect(["Enroll:step2", $email], $this->texty_presentera->translate("Enroll2Form_validate_er_code"), "warning");
      }

    }
    // aktivovat
    Logger::log( 'audit', Logger::INFO , 
        "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: AKTIVACIA $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

    $this->pv_user->updateUserEnrollState( $email, 10, 0, 3);
    $this->mailService->sendMailAdmin( 
        'Nový užívateľ',
        "<p>Užívateľ <b>{$email}</b> urobil self-enroll. </p>" );

    $this->flashRedirect(["Sign:in", $email], $this->texty_presentera->translate("Enroll2Form_validate_ok"), "success");
  }

}