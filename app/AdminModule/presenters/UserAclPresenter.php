<?php

namespace App\AdminModule\Presenters;

use PeterVojtech;

/**
 * Prezenter pre spravu ACL uzivatelov.
 * 
 * Posledna zmena(last change): 09.06.2023
 * @actions default
 *
 *	Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.4
 */
class UserAclPresenter extends BasePresenter
{

	use PeterVojtech\UserAcl\userAclEditTrait;

	/*protected $my_params;

	public function __construct($parameters)
	{
		$this->my_params = $parameters;
	}*/
}
