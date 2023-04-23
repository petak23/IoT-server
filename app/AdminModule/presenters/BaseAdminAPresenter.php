<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;
use App;
use App\Services\Logger;

/**
 * Last change 21.04.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
class BaseAdminAPresenter extends App\Presenters\BasePresenter
{
	use Nette\SmartObject;

	/** @var array nastavenie z config-u */
	public $nastavenie;

	// hodnoty z konfigurace
	public $appName;
	public $links;

	public function __construct(array $parameters = [])
	{
		// Nastavenie z config-u
		$this->nastavenie = $parameters;
		$this->links = $parameters['links'];
		$this->appName = $parameters['title'];
	}

	public function checkAcces(int $deviceUserId, string $type = "zařízení")
	{
		if ($this->getUser()->id != $deviceUserId) {
			Logger::log(
				'audit',
				Logger::ERROR,
				"Uzivatel #{$this->getUser()->id} {$this->getUser()->getIdentity()->username} zkusil editovat {$type} patrici uzivateli #{$deviceUserId}"
			);
			$this->getUser()->logout(true);
			$this->flashRedirect(self::DEFAULT_SIGN_IN_PAGE, "K tomuto {$type} nemáte práva!", "danger");
		}
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$user = $this->getUser();
		if ($user->isLoggedIn()) {
			$this->template->user_main = $this->user_main->getUser($user->id);
		}
		$this->template->appName = $this->appName;
		$this->template->links = $this->links;
	}
}
