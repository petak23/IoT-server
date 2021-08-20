<?php
declare(strict_types=1);

namespace PeterVojtech\UserAcl;

use App\Model;
use Nette\Application\UI\Form;
use Nette\Database;
use Nette\Security\User;

/**
 * Formular a jeho spracovanie pre pridanie a editaciu roly do ACL.
 * Posledna zmena 02.07.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class EditRoleFormFactory {
  
  /** @var Model\PV_User_roles */
	public $user_roles;
  /** @var string */
  private $prilohy_adresar;
  /** @var array */
  private $prilohy_images;
  /** @var int */
  private $id_user_main;
  /** @var string */
  private $wwwDir;
  
  /**
   * @param string $prilohy_adresar Cesta k adresaru pre ukladanie priloh od www adresara - Nastavenie priamo cez servises.neon
   * @param array $prilohy_images Nastavenie obrazkov pre prilohy - Nastavenie priamo cez servises.neon
   * @param string $wwwDir WWW adresar - Nastavenie priamo cez servises.neon
   * @param DbTable\Dokumenty $dokumenty
   * @param User $user */
  public function __construct(/*string $prilohy_adresar, array $prilohy_images, string $wwwDir,*/ 
                              Model\PV_User_roles $user_roles/*, User $user*/) {
    $this->user_roles = $user_roles;
    
    /*$this->dokumenty = $dokumenty;
    $this->id_user_main = $user->getId();
    $this->wwwDir = $wwwDir;
    $this->prilohy_adresar = $prilohy_adresar;
    $this->prilohy_images = $prilohy_images;*/
	}
  
  public function create(): Form  {
    $form = new Form();
		$form->addProtection();
    $form->addHidden("id");
    $form->addText('role', 'Názov roly:');
    $form->addSelect('inherited', 'Dedí od role:', $this->user_roles->findAll()->fetchPairs('id', 'name'))
         ->setPrompt('Nededí od žiadnej role');;
    $form->addText('name', 'Zobrazený názov úrovne registrácie:');
		$form->addSubmit('save', 'Ulož')
          ->setHtmlAttribute('class', 'btn btn-success')
          ->onClick[] = [$this, 'editRoleFormSubmitted'];
    $form->addSubmit('cancel', 'Cancel')
          ->setHtmlAttribute('class', 'btn btn-default')
          ->setHtmlAttribute('data-dismiss', 'modal')
          ->setHtmlAttribute('aria-label', 'Close')
          ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie formulara pre pridanie a editaciu prilohy polozky.
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulara 
   * @throws Database\DriverException   */
  public function editRoleFormSubmitted(\Nette\Forms\Controls\SubmitButton $button) {
		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara

    dumpe($values);
    /*try {
      $uloz = [ 
        'id_hlavne_menu'	 	=> $values->id_hlavne_menu,
        'id_user_main'      => $this->id_user_main,
        'id_user_roles'     => $values->id_user_roles,
        'description'				=> isset($values->description) && strlen($values->description)>2 ? $values->description : NULL,
        'change'						=> StrFTime("%Y-%m-%d %H:%M:%S", Time()),
        'type'              => $values->type
      ];
      $nazov = isset($values->name) ? $values->name : "";
      if ($values->priloha && $values->priloha->error == 0) { //Ak nahravam prilohu
        $priloha_info = $this->_uploadPriloha($values);
        $uloz = array_merge($uloz, [
          'name'				=> strlen($nazov)>2 ? $nazov : $priloha_info['finalFileName'],
          'web_name'  	=> Strings::webalize($priloha_info['finalFileName']),
          'pripona'			=> $priloha_info['pripona'],
          'main_file'		=> $this->prilohy_adresar.$priloha_info['finalFileName'].".".$priloha_info['pripona'],
          'thumb_file'	=> $priloha_info['thumb'],
          'type'        => $priloha_info['is_image'] ? 2 : $values->type
          ]);
      } elseif ($values->thumb->hasFile() && $values->thumb->isImage()) { //Ak nahravam len nahlad
        $uloz = array_merge($uloz, ['thumb_file'	=> $this->_uploadThumb($values)]);
      }  else { //Ak len menim
        $uloz = array_merge($uloz, ['name' => strlen($nazov)>2 ? $nazov : ""]);
      }
      $vysledok = $this->dokumenty->uloz($uloz, $values->id);
      if (!empty($vysledok) && isset($priloha_info['is_image']) && $priloha_info['is_image']) { $this->dokumenty->oprav($vysledok['id'], ['znacka'=>'#I-'.$vysledok['id'].'#']);}
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}*/
  }
}