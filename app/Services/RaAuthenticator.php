<?php

declare(strict_types=1);

namespace App\Services;

use App\Model;
use Nette;
use Nette\Security;
//use Nette\Utils\Strings;
use Nette\Utils\DateTime;
use App\Services\Logger;
use App\Exceptions\UserNotEnrolledException;

/**
 * @last_edited petak23<petak23@gmail.com> 20.08.2021
 */
class RaAuthenticator implements Security\Authenticator {
  private $passwords;
  private $request;

  /** @var Model\PV_User */
  private $pv_user;

  const NAME = 'audit';

	public function __construct(Model\PV_User $pv_user,
                              Security\Passwords $passwords, 
                              Nette\Http\Request $request ) {
    $this->pv_user = $pv_user;
    $this->passwords = $passwords;
    $this->request = $request;
  }

  private function badPasswordAction( $id, $badPwdCount, $lockoutTime, $ip, $browser ) {
    $this->pv_user->save( $id, [
        'id_rauser_state' => 91,
        'bad_pwds_count' => $badPwdCount,
        'locked_out_until' => $lockoutTime,
        'last_error_time' => new DateTime(),
        'last_error_ip' => $ip,
        'last_error_browser' => $browser,
      ]);
  }

  private function loginOkAction( $userData, $ip, $browser ) {
    $this->pv_user->save( $userData->id, [
        'id_rauser_state' => 10,
        'bad_pwds_count' => 0,
        'cur_login_time' => new DateTime(),
        'cur_login_ip' => $ip,
        'cur_login_browser' => $browser,
        'prev_login_time' => $userData->cur_login_time,
        'prev_login_ip' => $userData->cur_login_ip,
        'prev_login_browser' => $userData->cur_login_browser
      ]);
  }

	public function authenticate(string $username, string $password): Security\SimpleIdentity {
    $ip = $this->request->getRemoteAddress();
    $ua = $this->request->getHeader('User-Agent') . ' / ' . $this->request->getHeader('Accept-Language');

		//[$username, $password] = $credentials;

		$userData = $this->pv_user->getUserBy(['username' => $username]);

		if (!$userData) {
      Logger::log( self::NAME, Logger::ERROR , "[{$ip}] Login: nenalezen uzivatel {$username}, '{$ua}'" ); 
			throw new Security\AuthenticationException('Uživatel neexistuje.');
    }
        
    if( $userData->id_rauser_state == 1 ) {
      Logger::log( self::NAME, Logger::ERROR , "[{$ip}] Login: {$username} state=1 " ); 
      throw new UserNotEnrolledException('Tento účet ještě není aktivní, zadejte kód z e-mailu.');
    }  else if( $userData->id_rauser_state == 90 ) {
      Logger::log( self::NAME, Logger::ERROR , "[{$ip}] Login: {$username} state=90, '{$ua}'" ); 
      throw new Security\AuthenticationException('Tento účet byl správcem systému uzamčen.');
    } else if( $userData->id_rauser_state == 91 ) {
      $lockoutTime = (DateTime::from( $userData->locked_out_until ))->getTimestamp();
      if( $lockoutTime > time() ) {
        $rest = $lockoutTime - time();
        Logger::log( self::NAME, Logger::ERROR , "[{$ip}] Login: {$username} state=91 for {$rest} sec; '{$ua}'" ); 
        throw new Security\AuthenticationException("Tento účet je dočasně uzamčen, zkuste to znovu za {$rest} sekund." );
      }
    } else if( $userData->id_rauser_state == 10 ) {
      // OK, korektni stav
    }

		if (!$this->passwords->verify($password, $userData->phash)) {
      $badPwdCount = $userData->bad_pwds_count + 1;
      Logger::log( self::NAME, Logger::ERROR , "[{$ip}] Login: spatne heslo pro {$username}, badPwdCount={$badPwdCount}, '{$ua}'" ); 

      $delay = pow( 2, $badPwdCount );
      $lockoutTime = (new DateTime())->setTimestamp( time() + $delay );
      $this->badPasswordAction($userData->id, $badPwdCount, $lockoutTime, $ip, $ua);

			throw new Security\AuthenticationException('Špatné heslo.');
    }

    // pokud heslo potrebuje rehash, rehashnout
    if ($this->passwords->needsRehash($userData->phash)) {
      $this->pv_user->save($userData->id, ['phash' => $this->passwords->hash($password)]);
    }

    $this->loginOkAction( $userData, $this->request->getRemoteAddress(), $ua);
    
    Logger::log( self::NAME, Logger::INFO , "[{$ip}] Login: prihlasen {$username} v roli '{$userData->user_roles->name}', '{$ua}'" ); 

    //$roles = Strings::split($userData->role, '~,\s*~');
    $role = $userData->user_roles->role;
		return new Security\SimpleIdentity($userData->id, $role, ['username' => $userData->username, 'prefix' => $userData->prefix, 'id_user_roles' => $userData->id_user_roles ]);
	}
}