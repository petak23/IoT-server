<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Table\Selection;

/**
 * Model, ktory sa stara o tabulku updates
 * 
 * Posledna zmena 14.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2022 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class PV_Updates extends Table {

  /** @var string */
  protected $tableName = 'updates';

  public function getOtaUpdates(int $id): Selection
  {
    return $this->findBy(["device_id" => $id])->order("id ASC");
  }
}