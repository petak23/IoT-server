<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\User;
use App\Services;

final class HomepagePresenter extends MainBasePresenter {


  public function __construct(Services\Config $config ) {
    $this->links = $config->links;  // Definet in MainBasePresenter
  }
    
  public function actionDefault($email = NULL ): void {
    $response = $this->getHttpResponse();
    $response->setHeader('Cache-Control', 'no-cache');
    $response->setExpiration('1 sec'); 

    $this->email = $email;
  }

  public function beforeRender(): void {
    parent::beforeRender();
    $this->template->links = $this->links;
  }
    
}
