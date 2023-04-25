<?php

namespace PeterVojtech\User\Sign;

use Language_support;
use Nette;

/**
 * Komponenta pre zobrazenie prihlasovacieho formulára
 * Posledna zmena(last change): 20.04.2023
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.2
 */
class SignInControl extends Nette\Application\UI\Control
{

	/** @var Language_support\LanguageMain */
	public $texts;

	/** @var array */
	private $paramsFromConfig;
	/** @var string Skratka jazyka */
	private $language = 'sk';

	public $signInForm;

	/**
	 * @param string $language 
	 * @param Language_support\LanguageMain $texts */
	public function __construct(
		string $language,
		Language_support\LanguageMain $texts,
		SignInFormFactory $signInForm
	) {
		$this->language = $language;
		$this->texts = $texts;
		$this->texts->setLanguage($language);
		$this->signInForm = $signInForm;
	}

	/**
	 * Parametre z components.neon
	 * @param array $params
	 * @return FotogaleryControl */
	public function fromConfig(array $params)
	{
		$this->paramsFromConfig = $params;
		return $this;
	}

	protected function createComponentSignInForm(): Nette\Application\UI\Form
	{
		$pthis = $this->presenter;
		$form = $this->signInForm->create($this->language, $pthis->email);
		$form->onSuccess[] = function ($form) use ($pthis) {
			$pthis->restoreRequest($pthis->backlink); // https://pla.nette.org/cs/jak-po-odeslani-formulare-zobrazit-stejnou-stranku
			$this->flashMessage($this->texts->translate('SignInForm_login_ok'), 'success');
			$pthis->flashRedirect(':Admin:Inventory:user', $this->texts->translate('SignInForm_login_ok'), 'success');
		};

		return $form;
	}

	/** 
	 * Render funkcia pre vykreslenie fotogalérie
	 * @param array $p Parametre: id_hlavne_menu - id odkazovaneho clanku, template - pouzita sablona
	 * @see Nette\Application\Control#render() */
	public function render($p = [])
	{
		$this->template->setFile(__DIR__ . "/SignIn.latte");
		$this->template->setTranslator($this->texts);
		$this->template->reg_enabled = $this->paramsFromConfig["reg_enabled"];
		$this->template->render();
	}
}

interface ISignInControl
{
	function create(string $language): SignInControl;
}
