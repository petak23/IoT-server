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
 * @last_edited petak23<petak23@gmail.com> 24.08.2021
 */
class EnrollPresenter extends MainBasePresenter
{

  /** @var Model\PV_User @inject */
	public $pv_user;

  /** @var Passwords */
  private $passwords;

  private $mailService;

  /** @persistent */
  public $email = "";

  public $links;

  /** @var Forms\User\RegisterFormFactory @inject*/
	public $registerForm;

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
		$form = $this->registerForm->create($this->link("Sign:ForgottenPassword"), $this->language);
		$form->onSuccess[] =  [$this, 'enrollFormSucceeded'];
		return $form;
  }

  public function enrollFormSucceeded(Form $form, Nette\Utils\ArrayHash $values ): void
  {
    $hash = $this->passwords->hash($values->password);
    
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
      $this->flashRedirect("Enroll:step1", "Zadanou e-mail adresu již používá jiný uživatel. Zvolte prosím jinou.", "warning");
    }

    Logger::log( 'audit', Logger::INFO , 
        "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: zalozen {$values->email} prefix=[{$prefix}] [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 
    
    $mailUrl = $this->link("//Enroll:step2", ['email'=>$values->email, 'code'=>$code]); // Lomítka na zač. znamená absolútna adresa

    $this->mailService->sendMail( 
        $values->email,
        'Registrace',
        "<p>Dobrý den,</p>
        <p>Váš aktivační kód je: <b>{$code}</b><p>
        <p><a href=\"{$mailUrl}\">Kliknutím provedete aktivaci.</a></p>
        ");

    $this->flashRedirect(["Enroll:step2", $values->email], "Mail poslán na {$values->email}", "success");
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

  protected function createComponentStep2Form(): Form
	{
		$form = new Form;
		$form->addText('code', 'Kód z e-mailu:')
        ->setOption('description', 'zadejte kód z e-mailu')
        ->setHtmlAttribute("class", "form-control text")
        ->setRequired("Kód musí byť zadaný!");

    $form->addSubmit('send', 'Potvrdit účet')
        ->setHtmlAttribute("class", "btn btn-success btn-block")
        ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    $form->onSuccess[] = [$this, 'step2FormSucceeded'];
    
		return $form;
    }

    public function step2FormSucceeded(Form $form, Nette\Utils\ArrayHash $values ): void
    {
      $this->validujKod( $this->email, $values->code );
    }

    private function validujKod( $email, $code ) {

      $userdata = $this->pv_user->getUserBy(['email'=>$email]);

      if( !$userdata ) {
        Logger::log( 'audit', Logger::ERROR , 
            "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: nenalezen $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

        $this->flashRedirect("Enroll:step2", "Uživatel {$email} neexistuje.", "danger");
      }

      if( $userdata->id_rauser_state != 1) {
        Logger::log( 'audit', Logger::ERROR , 
            "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: spatny stav {$userdata->id_rauser_state} pro $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

        $this->flashRedirect(["Sign:in", $email], "Aktivační kód pro uživatele {$email} byl zadán, můžete se přihlásit.", "success");
      }

      if( $userdata->self_enroll_code !== $code) {

        if( $userdata->self_enroll_error_count >= 3 ) { 
          // smazat ucet
          Logger::log( 'audit', Logger::ERROR , 
              "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: MAZU UCET, spatny kod pro $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

          $this->pv_user->deleteUserByEmailEnroll( $email );
          $this->flashRedirect("Sign:in", "Opakovaně špatný kód. Tento účet byl smazán.", "danger");
        } else {
          // navysit pocet chyb
          Logger::log( 'audit', Logger::ERROR , 
              "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: spatny kod pro $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

          $this->pv_user->updateUserEnrollState( $email, 1, 1 + $userdata->self_enroll_error_count, 2 );
          $this->flashRedirect(["Enroll:step2", $email], "Špatný e-mail nebo kód.", "warning");
        }

      }
      // aktivovat
      Logger::log( 'audit', Logger::INFO , 
          "[{$this->getHttpRequest()->getRemoteAddress()}] Enroll: AKTIVACE $email [{$this->getHttpRequest()->getHeader('User-Agent')} / {$this->getHttpRequest()->getHeader('Accept-Language')}]" ); 

      $this->pv_user->updateUserEnrollState( $email, 10, 0, 3);
      $this->mailService->sendMailAdmin( 
          'Nový uživatel',
          "<p>Uživatel <b>{$email}</b> udělal self-enroll. </p>" );

      $this->flashRedirect(["Sign:in", $email], "Aktivace provedena, můžete se přihlásit.", "success");
    }

}