<?php
namespace PeterVojtech\User;

/**
 * Traita pre zobrazenie prihlasovacieho formulára
 * 
 * Posledná zmena(last change): 27.08.2021
 * 
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
trait signInTrait {
  /** @var ISignInControl @inject */
  public $signInControlFactory;

  /** 
   * Vytvorenie komponenty 
   * @return SignInControl */
	public function createComponentSignIn() {
      $out = $this->signInControlFactory->create($this->language);
      
      return $out->fromConfig($this->my_params['components']['signIn']); //Vrati komponentu aj s nastaveniami z components.neon
	}
}