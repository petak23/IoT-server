<?php

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku rausers
 * 
 * Posledna zmena 31.05.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class User extends Table {

  /** @var string */
  protected $tableName = 'rausers';

  public function getUsers() { 
    return $this->findAll()->order('username ASC');
    /*return $this->database->fetchAll(  '
        select u.* , us.desc
        from rausers u
        left outer join rauser_state us
        on u.state_id = us.id
        order by username asc
    ' );*/
  }

  public function getUser( $id ) {
    return $this->find($id);
  }
}