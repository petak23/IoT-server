<?php

declare(strict_types=1);

namespace App\Services;

use Nette;
use Nette\Utils\DateTime;
use Nette\Utils\Random;
use Tracy\Debugger;

use App\Model;
use \App\Model\Device;
use \App\Model\Devices;
use \App\Model\View;
use \App\Model\ViewItem;

/**
 * @last_edited petak23<petak23@gmail.com> 19.07.2021
 * @deprecated
 */
class EnrollDataSource 
{
    use Nette\SmartObject;
    
    private $database;

    private $pv_user;
    
	public function __construct(Nette\Database\Explorer $database, Model\PV_User $PV_User) {
    $this->database = $database;
    $this->pv_user = $PV_User;
	}

    /**
     * @deprecated 
     */
    public function updateUser( $email, $status, $errCount )
    {
        $this->database->query('UPDATE rausers SET ', [ 
            'id_rauser_state' => $status,
            'self_enroll_error_count' => $errCount
        ] , 'WHERE email = ? AND id_rauser_state = 1', $email);
    }

    /**
     * @deprecated 
     */
    public function deleteUser( $email )
    {
        $this->database->query('DELETE from rausers WHERE username = ? and id_rauser_state = 1', $email);
    }
    
    /**
     * @deprecated 
     */
    public function createUser( $values, $hash, $prefix, $code )
    {
      $user = $this->database->fetch(  '
          select * from rausers
          where email = ?
      ', $values['email'] );

      if( $user!=NULL ) {
          return -1;
      }

      $this->database->query('INSERT INTO rausers ', [
          'username' => $values['email'],
          'phash' => $hash,
          'role' => 'user',
          'email' => $values['email'],
          'prefix' => $prefix,
          'id_rauser_state' => 1,
          'self_enroll' => 1,
          'self_enroll_code' => $code,
          'measures_retention' => 90,
          'sumdata_retention' => 366,
          'blob_retention' => 7,
          'monitoring_token' => Random::generate(40)
      ]);

      return 0;
    }

    /**
     * @deprecated 
     */
    public function getPrefix( $prefix )
    {
        return $this->database->fetch(  '
            select * from rausers
            where prefix = ?
        ', $prefix );
    }
    
    /**
     * @deprecated 
     */
    public function getUserByUsername( $email )
    {
        return $this->database->fetch(  '
            select * from rausers
            where username = ?
        ', $email );
    }
}


