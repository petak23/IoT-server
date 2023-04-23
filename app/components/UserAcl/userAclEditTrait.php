<?php

namespace PeterVojtech\UserAcl;

/**
 * Traita pre editáciu ACL
 * 
 * Posledna zmena(last change): 21.04.2023
 * 
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.1
 */
trait userAclEditTrait
{

	/** @var IAdminUserAcl @inject */
	public $adminUserAclControlFactory;

	/** 
	 * Vytvorenie komponenty 
	 * @return adminUserAclControl */
	public function createComponentUserAclEdit()
	{

		$out = $this->adminUserAclControlFactory->create();

		return $out->fromConfig($this->nastavenie['components']['userAclEdit']); //Vrati komponentu aj s nastaveniami z komponenty.neon
	}
}
