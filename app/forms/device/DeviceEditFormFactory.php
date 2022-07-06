<?php

namespace App\Forms\Device;

use App\Model;
use App\Services\Config;
use App\Services\Logger;
use Language_support;
use Nette\Application\UI;
use Nette\Utils\Random;
use Nette\Security;

/**
 * Formular pre pridanie a editáciu zariadenia
 * Posledna zmena 04.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
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
   * @return UI\Form */
  public function create(string $language, $id): UI\Form {
    $this->texts->setLanguage($language);
    $this->id = $id;
    $form = new UI\Form;
    $form->addProtection();
    $form->setTranslator($this->texts);

    $form->addGroup('device_form_gr_1');

    $form->addText('name', 'device_form_name')
        ->setRequired('device_form_name_sr')
        ->addRule(UI\Form::PATTERN, 'device_form_name_ar', '([0-9A-Za-z]+)')
        ->setOption('description', 'device_form_name_de')
        ->setHtmlAttribute('size', 50);

    $form->addText('passphrase', 'device_form_passphrase')
        ->setHtmlAttribute('size', 50)
        ->setRequired('device_form_passphrase_sr');

    $form->addTextArea('desc', 'device_form_desc')
        ->setHtmlAttribute('rows', 4)
        ->setHtmlAttribute('cols', 50)
        ->setRequired('device_form_desc_sr');

    $form->addGroup('device_form_gr_2');

    $form->addText('json_token', 'device_form_json_token')
        ->setOption('description', 'device_form_json_token_de')
        ->setHtmlAttribute('size', 50)
        ->setDefaultValue( Random::generate(40) );

    $form->addText('blob_token', 'device_form_blob_token')
        ->setOption('description', 'device_form_blob_token_de')
        ->setHtmlAttribute('size', 50)
        ->setDefaultValue( Random::generate(40) );

    $form->addGroup('device_form_gr_3');

    $form->addCheckbox('monitoring', 'device_form_monitoring')
        ->setOption('description', 'device_form_monitoring_de')
        ->setDefaultValue(false);

    $form->addSubmit('send', 'base_save')
        ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    $form->onSuccess[] = [$this, 'formSucceeded'];
    return $form;
  }

  /** 
   * 
   */
  public function formSucceeded(UI\Form $form, array $values) {

    $values['name'] = $this->user->getIdentity()->prefix.":".$values['name'];
    $values['user_id'] = $this->user->id;
    $values['passphrase'] = $this->config->encrypt( $values['passphrase'], $values['name'] );

    if( $this->id ) {
        // editace
        $device = $this->pv_devices->getDevice( $this->id );
        if (!$device) {
          $form->addError($this->texts->translate('devices_not_found_h1'));
        } else if( $this->user->id != $device->user_id ) {
          Logger::log( 'audit', Logger::ERROR , 
            sprintf($this->texts->translate('log_device_edit_not_aut'), $this->user->id, $this->user->getIdentity()->email, $device->user_id));
          $this->user->logout(true);
          $form->addError($this->texts->translate('device_form_not_aut'), "danger");
        } else {
          $device->update( $values );
        }
    } else {
        // zalozeni
        $this->pv_devices->createDevice( $values );
    }
	}
}
