<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model;
use App\Services\Logger;

/**
 * @last_edited petak23<petak23@gmail.com> 23.07.2021
 */
class BaseAdminPresenter extends BasePresenter
{
    use Nette\SmartObject;

    /** @var Model\PV_User @inject */
	public $userInfo;

  public function checkAcces(int $deviceUserId, string $type="zařízení" ) {
    if( $this->getUser()->id != $deviceUserId ) {
        Logger::log( 'audit', Logger::ERROR , 
            "Uzivatel #{$this->getUser()->id} {$this->getUser()->getIdentity()->username} zkusil editovat {$type} patrici uzivateli #{$deviceUserId}" );
        $this->getUser()->logout();
        $this->flashMessage("K tomuto {$type} nemáte práva!");
        $this->redirect('Sign:in');
    }
  }

    // hodnoty z konfigurace
    public $appName;
    public $links;

    public function populateTemplate( $activeItem, $submenuAfterItem = FALSE, $submenu = NULL )
    {
      $this->template->appName = $this->appName;
      $this->template->links = $this->links;
      $this->template->path = "";

      $this->populateMenu( $activeItem, $submenuAfterItem, $submenu );
    }

    public function beforeRender() {
      $user = $this->getUser();
      if ($user->isLoggedIn()) {
        $this->template->userInfo = $this->userInfo->getUser($user->id);
      }
      $this->template->appName = $this->appName;
      $this->template->links = $this->links;
      $this->template->path = "";
    }

}