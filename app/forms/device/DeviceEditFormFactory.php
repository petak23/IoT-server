<?php

namespace App\Forms\Device;

use App\Model;
use App\Services\Config;
use App\Services\Logger;
use Language_support;
use Nette\Application\UI\Form;
use Nette\Utils\Random;
use Nette\Security;

/**
 * Formular pre pridanie a editáciu zariadenia
 * Posledna zmena 17.09.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class DeviceFormFactory {
  /** @var Security\User */
  private $user;
  /** @var Language_support\LanguageMain */
  private $texts;
  /** @var Model\PV_Devices */
  private $pv_devices;
  /** @var int */
  private $id;
  /** @var Config */
  private $config;

  /** @param Security\User $user   */
  public function __construct(Security\User $user,
                              Language_support\LanguageMain $language_main, 
                              Model\PV_Devices $pv_devices,
                              Config $config) {
    $this->user = $user;
    $this->texts = $language_main;
    $this->pv_devices = $pv_devices;
    $this->config = $config;
	}
  
  /**
   * Formular
   * @return Nette\Application\UI\Form */
  public function create(string $language, $id): Form {
    $this->texts->setLanguage($language);
    $this->id = $id;
    $form = new Form;
    $form->addProtection();
    $form->setTranslator($this->texts);

    $form->addGroup('Základní údaje');

    $form->addText('name', 'Identifikátor (jméno):')
        ->setRequired()
        ->addRule(Form::PATTERN, 'Jen písmena a čísla', '([0-9A-Za-z]+)')
        ->setOption('description', 'Toto jméno doplněné prefixem bude používáno pro přihlašování zařízení.'  )
        ->setHtmlAttribute('size', 50);

    $form->addText('passphrase', 'Komunikační heslo:')
        ->setHtmlAttribute('size', 50)
        ->setRequired();

    $form->addTextArea('desc', 'Popis:')
        ->setHtmlAttribute('rows', 4)
        ->setHtmlAttribute('cols', 50)
        ->setRequired();

    $form->addGroup('Přístup k datům bez přihlášení');

    $form->addText('json_token', 'Bezpečnostní token pro data:')
        ->setOption('description', 'Pokud je vyplněn, kdokoli se znalostí správné adresy se může podívat na JSON s daty. Má smysl jen v případě, že má zařízení nějaké senzory.'  )
        ->setHtmlAttribute('size', 50)
        ->setDefaultValue( Random::generate(40) );

    $form->addText('blob_token', 'Bezpečnostní token pro galerii:')
        ->setOption('description', 'Pokud je vyplněn, kdokoli se znalostí správné adresy se může podívat na galerii obrázků. Má smysl jen tehdy, pokud zařízení nahrává obrázky.'  )
        ->setHtmlAttribute('size', 50)
        ->setDefaultValue( Random::generate(40) );

    $form->addGroup('Monitoring');

    $form->addCheckbox('monitoring', 'Zařadit do monitoringu funkce')
        ->setOption('description', 'Pokud ze senzorů zařízení nebudou chodit data tak často, jak mají, bude zaslána notifikace.'  )
        ->setDefaultValue(false);

    $form->addSubmit('send', 'Uložit')
        ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    //$form->onValidate[] = [$this, 'validateForm'];
    $form->onSuccess[] = [$this, 'formSucceeded'];

    //$this->makeBootstrap4( $form );
    return $form;
  }

  /** 
   * 
   */
  public function formSucceeded(Form $form, array $values) {
    $values['name'] = "{$this->user->getIdentity()->prefix}:{$values['name']}";
    $values['user_id'] = $this->user->id;

    $values['passphrase'] = $this->config->encrypt( $values['passphrase'], $values['name'] );

    if( $this->id ) {
        // editace
        $device = $this->pv_devices->getDevice( $this->id );
        if (!$device) {
          $form->addError('Zařízení nebylo nalezeno');
        }
        else if( $this->user->id != $device->user_id ) {
          Logger::log( 'audit', Logger::ERROR , 
              "Uzivatel #{$this->user->id} {$this->user->getIdentity()->username} zkusil editovat zařízení patrici uzivateli #{$device->user_id}" );
          $this->user->logout(true);
          $form->addError("K tomuto zařízení nemáte práva!", "danger");
        } else {
          $device->update( $values );
        }
    } else {
        // zalozeni
        $this->pv_devices->createDevice( $values );
    }
	}
}
