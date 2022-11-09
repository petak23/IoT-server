<?php

declare(strict_types=1);

namespace App\Model;

use App\Components\Menu;
use Nette;

/**
 * Model starajuci sa o tabulku main_menu
 * 
 * Posledna zmena 01.08.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class PV_Main_menu extends Table {
  /** @var string */
  protected $tableName = 'main_menu';

  /**
   * @param Nette\Database\Explorer $db
   * @param Nette\Security\User $user */
  public function __construct(Nette\Database\Explorer $db, Nette\Security\User $user)  {
    parent::__construct($db);
    $this->user = $user;
	}

  /** 
   * Vypis menu 
   * @return array */
	public function getMenu(string $language): array {	
    $mm = clone $this;
    $mm_out = $mm->findAll()->order('id ASC');
    return count($mm_out) ? $this->_getMenu($mm_out) : [];
  }
  
  /** 
   * Vytvorenie menu pre front
   * @param Nette\Database\Table\Selection $pol Vyber poloziek hl. menu
   * @return array */
  private function _getMenu(Nette\Database\Table\Selection $pol): array {
    $out = [];
    foreach ($pol as $p) {
      $to_acl = explode(":", $p->link);
      if ( $this->user->isAllowed($to_acl[0], isset($to_acl[1]) ? $to_acl[1] : null) ) {
        $temp_pol = new Menu\MenuNode;
        $temp_pol->name = $p->name;
        $temp_pol->link = $p->link;
        $temp_pol->id = $p->id;
        $out[] = ["node"=>$temp_pol, "nadradena" => null /*isset($v->id_nadradenej) ? $v->id_nadradenej : -1*$v->hlavne_menu_cast->id*/];
        unset($temp_pol);
      }
    }
    return $out;
  }  
  
}