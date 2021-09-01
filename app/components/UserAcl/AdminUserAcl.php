<?php
namespace PeterVojtech\UserAcl;

use App\Model;
use Nette;
//use Ublaboo\DataGrid\DataGrid;
//use Ublaboo\DataGrid\Column\Action\Confirmation;

/**
 * Komponenta pre editáciu ACL
 * Posledna zmena(last change): 20.08.2021
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.2
 */
class AdminUserAcl extends Nette\Application\UI\Control {

  /** @var Model\PV_User_permission */
	public $user_permission;

  /** @var Model\PV_User_roles */
	public $user_roles;

  /** @var Model\PV_User_resource */
  public $user_resource;

  /** @var Nette\Database\Table\Selection */
  protected $articles;
  
  /** @var array */
  private $paramsFromConfig;

  /** &var EditRoleFormFactory */
  //public $editRoleForm;
  
  public function __construct(Model\PV_User_permission $user_permission,
                              Model\PV_User_roles $user_roles,
                              Model\PV_User_resource $user_resource,
                              //EditRoleFormFactory $editRoleFormFactory,
  ) {
    $this->user_permission = $user_permission;
    $this->user_roles = $user_roles;
    $this->user_resource = $user_resource;
    //$this->editRoleForm = $editRoleFormFactory;
  }

  /**
   * Parametre z komponenty.neon
   * @param array $params
   * @return AdminAktualneOznamyControl */
  public function fromConfig(array $params) {
    $this->paramsFromConfig = $params;
    return $this;
  }
  
  /** 
   * Render
   * @param array $p Parametre: template - pouzita sablona
   * @see Nette\Application\Control#render() */
  public function render($p = []) {
    $this->template->setFile(__DIR__ . "/AdminUserAcl_default.latte");
    $this->template->user_permission = $this->user_permission->findAll();
    $this->template->user_roles = $this->user_roles->findAll();
    $this->template->user_resource = $this->user_resource->findAll();
    $this->template->render();
  }

  /** 
   * Komponenta formulara pre pridanie a editaciu roly.
   * @return Nette\Application\UI\Form */
  /*public function createComponentEditRoleForm(): Nette\Application\UI\Form {
    $form = $this->editRoleForm->create();
    //$form->setDefaults(["id"=>0, "id_hlavne_menu"=>$this->clanok->id_hlavne_menu, "id_user_roles"=>$this->clanok->hlavne_menu->id_user_roles]);
    $form['save']->onClick[] = function ($button) { 
      //$this->presenter->flashOut(!count($button->getForm()->errors), ['this',['tab'=>'prilohy-tab']], 'Príloha bola úspešne uložená!', 'Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...');
      //if (!count($button->getForm()->errors)) {
      //  $this->presenter->flashRedirect(['this',['tab'=>'prilohy-tab']], 'Príloha bola úspešne uložená!', 'success');
      //} else {
      //  $this->flashMessage('Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...', 'danger');
      //  $this->flashMessage($button->getForm()->errors);
      //}
		};
    return $this->presenter->makeBootstrap4($form);
  }*/

  /** 
   * Datagrid pre uzivatelov
   * @param type $name
   * @return DataGrid */
  /*public function createComponentUserResourcesGrid($name) {
		$grid = new DataGrid($this, $name);
    $grid->setDataSource($this->user_resource->findAll()->order('id ASC'));
    $grid->setTemplateFile(__DIR__ . '/custom_datagrid_template.latte');
    $grid->addColumnNumber('id', 'Id')->setAlign('right')->setFormat(0)->addCellAttributes(['style' => 'width: 3rem;']);
    $grid->addColumnText('name', 'name; Zdroj pre ACL')
          ->setEditableCallback(function($id, $value) {
            $this->user_resource->oprav($id, ['name'=>$value]);
          });
    //$grid->addAction('delete', '', 'confirmedDeleteClen!')
    //      ->setIcon('trash-alt')->setTitle('Zmazať užívateľa')->setClass('btn btn-xs btn-danger ajax')
    //      ->setConfirmation(
    //          new Confirmation\CallbackConfirmation(
    //            function($item) {
    //              return "Naozaj chceš zmazať užívateľa: '".$item->meno . ' ' . mb_strtoupper($item->priezvisko)."'?";
    //            }
    //          )
    //        );
    return $grid;
	}*/

}

interface IAdminUserAcl {
  /** @return AdminUserAcl */
  function create();
}