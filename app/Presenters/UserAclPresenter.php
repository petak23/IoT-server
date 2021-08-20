<?php
namespace App\Presenters;

use PeterVojtech;

/**
 * Prezenter pre spravu ACL uzivatelov.
 * 
 * Posledna zmena(last change): 17.08.2021
 * @actions default
 *
 *	Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.2
 */
class UserAclPresenter extends BaseAdminPresenter {

  use PeterVojtech\UserAcl\userAclEditTrait;

  protected $my_params;

  public function __construct($parameters) {
		$this->my_params = $parameters;
    $this->links = $parameters['links'];
		$this->appName = $parameters['title'];
  }
}