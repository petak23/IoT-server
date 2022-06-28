<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\User;
use App\Services;
use PeterVojtech;

final class HomepagePresenter extends MainBasePresenter {

  use PeterVojtech\User\signInTrait;

  public $email = '';

  protected $my_params;

  public function __construct($parameters, Services\Config $config ) {
    $this->links = $config->links;  // Definet in MainBasePresenter
    $this->my_params = $parameters;
  }
  
  /**
   * @var string $email pre prípad prednastavenia */  
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
