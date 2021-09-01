<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model;
use Nette;
use Tracy\Debugger;
use Nette\Utils\DateTime;
use Nette\Utils\Arrays;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Http\Url;
use Nette\Security\Passwords;

use \App\Services\InventoryDataSource;


/**
 * @last_edited petak23<petak23@gmail.com> 03.09.2021
 */
final class InventoryPresenter extends BaseAdminPresenter
{
  use Nette\SmartObject;

    /** @persistent */
	public $viewid = "";
    
  /** @var \App\Services\InventoryDataSource */
  private $datasource;

  /** @var Nette\Security\Passwords */
  private $passwords;

  // Database tables	
  /** @var Model\PV_Units @inject */
	public $units;
  /** @var Model\PV_Devices @inject */
	public $devices;

  public function __construct( InventoryDataSource $datasource,
                              \App\Services\Config $config )
  {
    $this->datasource = $datasource;
    $this->links = $config->links;
    $this->appName = $config->appName;
  }

  public function injectPasswords( Nette\Security\Passwords $passwords )
  {
    $this->passwords = $passwords;
  }


  // Seznam  Zařízení

  // http://lovecka.info/ra/inventory/home/
  public function renderHome()
  {
    $this->populateTemplate( 1 );
    $this->template->devices = $this->devices->getDevicesUser( $this->getUser()->id );
    $this->template->user_id = $this->getUser()->id;
  }

  public function renderUnits() {
    //$this->populateTemplate( 5 );
    $this->template->units = $this->units->getUnits();
  }


  // http://lovecka.info/ra/inventory/user/
  public function renderUser()
  {
    $this->populateTemplate( 3 );
    $this->template->userData = $this->datasource->getUser( $this->getUser()->id );

    if( $this->template->userData['prev_login_ip']!=NULL ) {
      $this->template->prev_login_name = gethostbyaddr( $this->template->userData['prev_login_ip'] );
      if( $this->template->prev_login_name===$this->template->userData['prev_login_ip'] ) {
        $this->template->prev_login_name = NULL;
      }
    }
    if( $this->template->userData['last_error_ip']!=NULL ) {
      $this->template->last_error_name = gethostbyaddr( $this->template->userData['last_error_ip'] );
      if( $this->template->last_error_name===$this->template->userData['last_error_ip'] ) {
        $this->template->last_error_name = NULL;
      }
    }


    $url = new Url( $this->getHttpRequest()->getUrl()->getBaseUrl() );
    $this->template->monitoringUrl = $url->getAbsoluteUrl() . "monitor/show/{$this->template->userData['monitoring_token']}/{$this->getUser()->id}/";
  }
   
  public function actionEdit(): void {

    $post = $this->userInfo->getUser( $this->getUser()->id );
    if (!$post) {
        $this->error('Uživatel nebyl nalezen');
    }
    $this['userForm']->setDefaults($post);
  
  }

  protected function createComponentUserForm(): Form
  {
    $form = new Form;
    $form->addProtection();

    $form->addEmail('email', 'E-mail adresa:')
        ->setRequired("E-mailová adresa musí byť zadaná!")
        ->setOption('description', 'Na tuto adresu se zasílají e-mail notifikace.'  )
        ->setHtmlAttribute('size', 50);

    $form->addText('monitoring_token', 'Bezpečnostní token pro výstup monitoringu:')
        ->setOption('description', 'Pokud není vyplněn, data nejsou bez autorizace dostupná.'  )
        ->setHtmlAttribute('size', 50);

    $form->addSubmit('send', 'Uložit')
         ->setHtmlAttribute('class', 'btn btn-success')
         ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');
        
    $form->onSuccess[] = [$this, 'userFormSucceeded'];

    return $this->makeBootstrap4( $form );
  }

  
  public function userFormSucceeded(Form $form, array $values): void {
    $this->userInfo->updateUser( $this->getUser()->id, $values );
    $this->flashRedirect("Inventory:user", "Změny provedeny.", "success");
  }
  

  public function actionPassword(): void {
    //$this->checkUserRole( 'user' );
    //$this->populateTemplate( 3 );
  }

  protected function createComponentPasswordForm(): Form {
    $form = new Form;
    $form->addProtection();

    $form->addPassword('oldPassword', 'Stávající heslo:')
        ->setOption('description', 'Vyplňte své aktuálně platné heslo.')
        ->setRequired();

    $form->addPassword('password', 'Nové heslo:')
        ->setOption('description', 'Nové heslo, které chcete nastavit.')
        ->setRequired();

    $form->addPassword('password2', 'Opakujte nové heslo:')
        ->setOption('description', 'Zadejte nové heslo ještě jednou.')
        ->setRequired('Zadajte, prosím, heslo ešte raz pre kontrolu')
        ->addRule($form::EQUAL, 'Heslo musí být zadáno dvakrát stejně!', $form['password'])
        ->setOmitted(); // https://doc.nette.org/cs/3.1/form-presenter#toc-validacni-pravidla

    $form->addSubmit('send', 'Změnit heslo')
         ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');
        
    $form->onSuccess[] = [$this, 'passwordFormSucceeded'];
    
    return $this->makeBootstrap4( $form );
  }

    
  public function passwordFormSucceeded(Form $form, array $values): void {
    
    $post = $this->userInfo->getUser( $this->getUser()->id );
    if (!$post) {
      $this->error('Uživatel nebyl nalezen');
    }
    if (!$this->passwords->verify($values['oldPassword'], $post->phash)) {
      $form->addError('Neplatné heslo - zadejte správné stávající heslo.');
      return;
    }

    $hash = $this->passwords->hash($values['password']);
    $this->userInfo->updateUserPassword( $this->getUser()->id, $hash );

    $this->flashMessage('Změna hesla provedena.');
    $this->redirect("Inventory:user" );
  }   
}