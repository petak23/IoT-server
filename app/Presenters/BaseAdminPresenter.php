<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model;
use App\Services\Logger;

/**
 * Last change 08.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class BaseAdminPresenter extends BasePresenter
{
  use Nette\SmartObject;

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

  // hodnoty z konfigurace
  public $appName;
  public $links;

  /**
   * @deprecated 
   */
  public function populateTemplate($activeItem, $submenuAfterItem = FALSE, $submenu = NULL)
  {
    $this->template->appName = $this->appName;
    $this->template->links = $this->links;
    $this->template->path = "";

    $this->populateMenu($activeItem, $submenuAfterItem, $submenu);
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
    $this->template->path = "";
  }
}
