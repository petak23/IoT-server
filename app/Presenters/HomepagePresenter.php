<?php

declare(strict_types=1);

namespace App\Presenters;

final class HomepagePresenter extends MainBasePresenter
{
  //public function actionDefault(): void
  //{
  protected function startup()
  {
    parent::startup();
    if ($this->getUser()->isLoggedIn()) {
      $this->redirect("Inventory:user");
    } else {
      $this->redirect(self::DEFAULT_SIGN_IN_PAGE);
    }
  }
}
