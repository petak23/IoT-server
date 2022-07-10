<?php

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku blobs
 * 
 * Posledna zmena 08.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class PV_Blobs extends Table
{

  /** @var string */
  protected $tableName = 'blobs';

  public function getBlobCount(int $deviceId): int
  {
    return $this->findBy(['device_id' => $deviceId, 'status > 0'])->count('*');
  }
}
