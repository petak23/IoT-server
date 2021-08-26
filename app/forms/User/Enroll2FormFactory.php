<?php
declare(strict_types=1);

namespace App\Forms\User;

use App\Model;
use Language_support;
use Nette\Application\UI\Form;
use Nette\Security\User;

/**
 * Formular pre potvrdenie emailu po registrácii
 * Posledna zmena 25.08.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class Enroll2FormFactory {

  public function __construct(Language_support\LanguageMain $language_main) {
    $this->texts = $language_main;
	}
  
  public function create(string $language):Form  {
    $this->texts->setLanguage($language);

    $form = new Form();
    $form->setRenderer(new UserFormRenderer);
		$form->addProtection();
    $form->setTranslator($this->texts);
		
		$form->addText('code', 'Enroll2Form_code')
        ->setOption('description', $this->texts->translate('Enroll2Form_code_de'))
        ->setHtmlAttribute("class", "form-control text")
        ->setRequired('Enroll2Form_code_sr');

    $form->addSubmit('send', 'Potvrdit účet')
        ->setHtmlAttribute("class", "btn btn-success btn-block")
        ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    //$form->onSuccess[] = [$this, 'step2FormSucceeded'];
    
		return $form;
	} 
}