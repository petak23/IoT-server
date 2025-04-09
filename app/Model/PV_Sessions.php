<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

/**
 * Model, ktory sa stara o tabulku sessions
 * 
 * Posledna zmena 09.04.2025
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2025 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class PV_Sessions extends Table
{

  /** @var string */
  protected $tableName = 'sessions';

  public function deleteSession(int $id): void
  {
    $this->findBy(["device_id" => $id])->delete();
  }

  public function getSessionById(int $sessionId) : ActiveRow|null 
  {
    return $this->find($sessionId);
  }
}
