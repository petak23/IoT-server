<?php

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku value_types
 * 
 * Posledna zmena 01.06.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class Units extends Table {

  /** @var string */
  protected $tableName = 'value_types';

  public function getUnits() { 
    return $this->findAll()->order('id ASC');
  }
}