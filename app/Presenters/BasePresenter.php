<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\Menu;
use App\Model;
use Nette;
use Tracy\Debugger;
use App\Services\Logger;

/**
 * Last change 16.09.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class BasePresenter extends MainBasePresenter
{
  use Nette\SmartObject;

  /** @var Model\PV_Main_menu @inject */
	public $main_menu;

  /**
   * @deprecated 
   */
  public function checkUserRole( $reqRole ) {}

  private function addMenuItem( $vals, $submenuAfterItem = FALSE, $submenu = NULL ) {
    $this->template->menu[] = $vals;
    if( $vals['id']==$submenuAfterItem ) {
      foreach( $submenu as $item ) {
        $this->template->menu[] = $item;
      }
    }
  }

  public function populateMenu( $activeItem, $submenuAfterItem = FALSE, $submenu = NULL ) {
    $this->template->menu = array();

    /*$this->addMenuItem( ['id' => '3', 'link' => 'inventory/user', 'name' => 'Můj účet'],
        $submenuAfterItem , $submenu );

    $this->addMenuItem(  ['id' => '1', 'link' => 'inventory/home', 'name' => 'Zařízení'],
        $submenuAfterItem , $submenu );
    $this->addMenuItem( ['id' => '2', 'link' => 'view/views', 'name' => 'Grafy'],
        $submenuAfterItem , $submenu );
    $this->addMenuItem( ['id' => '5', 'link' => 'inventory/units', 'name' => 'Kódy jednotek'],
        $submenuAfterItem , $submenu );

    if( $this->getUser()->isInRole('admin') ) {
      $this->addMenuItem( ['id' => '6', 'link' => 'user/list', 'name' => 'Uživatelé'],
      $submenuAfterItem , $submenu );
      $this->addMenuItem( ['id' => '7', 'link' => 'useracl', 'name' => 'Editácia ACL'],
        $submenuAfterItem , $submenu );
    }
      

    $this->template->menuId = $activeItem ;

    $response = $this->getHttpResponse();
    $response->setHeader('Cache-Control', 'no-cache');
    $response->setExpiration('1 sec');*/ 
  }

  /** 
   * Komponenta pre vykreslenie menu
   * @return Menu\Menu */
  public function createComponentMenu(): Menu\Menu {
    $menu = new Menu\Menu;
    $hl_m = $this->main_menu->getMenu(/*$this->language*/);
    if (count($hl_m)) {
      $servise = $this;
      $menu->fromTable($hl_m, function($node, $row) use($servise) {
      $poll = ["id", "name", "view_name"];
        foreach ($poll as $v) { $node->$v = $row['node']->$v; }
        $node->link = is_array($row['node']->link) ? $servise->link($row['node']->link[0], ["id"=>$row['node']->id]) 
                                                    : $servise->link($row['node']->link);
        return $row['nadradena'] ? $row['nadradena'] : null;
      });
    }
    $menu->selectByUrl($this->link('this'));
    return $menu;
  }            
}