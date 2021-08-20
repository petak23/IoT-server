<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku rauser_state
 * 
 * Posledna zmena 28.06.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class PV_User_state extends \App\Model\Table {

  /** @var string */
  protected $tableName = 'rauser_state';


  public function getAllForForm(): array {
    return $this->findAll()->fetchPairs('id', 'desc');
  }
}