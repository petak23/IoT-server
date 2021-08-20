<?php

namespace PeterVojtech\UserAcl;

/**
 * Traita pre editáciu ACL
 * 
 * Posledna zmena(last change): 02.07.2021
 * 
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
 trait userAclEditTrait {
  
  /** @var IAdminUserAcl @inject */
  public $adminUserAclControlFactory;
  
  /** 
   * Vytvorenie komponenty 
   * @return adminUserAclControl */
	public function createComponentUserAclEdit() {

    $out = $this->adminUserAclControlFactory->create();

    return $out->fromConfig($this->my_params['components']['userAclEdit']); //Vrati komponentu aj s nastaveniami z komponenty.neon
	}
}