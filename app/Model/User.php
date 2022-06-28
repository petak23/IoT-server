<?php

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku user_main
 * 
 * Posledna zmena 06.09.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class User extends Table {

  /** @var string */
  protected $tableName = 'user_main';

  public function getUsers() { 
    return $this->findAll()->order('username ASC');
  }

  public function getUser( $id ) {
    return $this->find($id);
  }
}