<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model;
use App\Services\Logger;
use Nette;
use Nette\Application\UI\Form;
use Language_support;

/**
 * Zakladny presenter pre vsetky presentery
 * 
 * Posledna zmena(last change): 14.07.2022
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link      http://petak23.echo-msz.eu
 * @version 1.0.6
 */
class MainBasePresenter extends Nette\Application\UI\Presenter
{

  use Nette\SmartObject;

  const DEFAULT_SIGN_IN_PAGE = 'Sign:in';

  /** @var Language_support\LanguageMain @inject */
  public $texty_presentera;

  public $links;

  /** @var string kmenovy nazov stranky pre rozne ucely typu www.neco.sk*/
  public $site_name;

  /** @var string Skratka aktualneho jazyka 
   * @persistent */
  public $language = 'sk';

  /** @persistent */
  public $backlink = '';

  /** @var Nette\Http\Request @inject*/
  public $httpRequest;

  // Database tables	
  /** @var Model\PV_User_main @inject */
  public $user_main;

  protected function startup()
  {
    parent::startup();

    // Nacitanie uzivatela
    $user = $this->getUser();

    $httpR = $this->httpRequest->getUrl();
    $this->site_name = $httpR->host . $httpR->scriptPath; // Nazov stranky v tvare www.nieco.sk
    $this->site_name = substr($this->site_name, 0, strlen($this->site_name) - 1);
    //dumpe($user->isLoggedIn());
    // Kontrola prihlasenia
    if ($user->isLoggedIn()) { //Prihlaseny uzivatel
      if (!$user->isAllowed($this->name, $this->action)) { //Kontrola ACL
        dumpe($this->name, $this->action);
        Logger::log(
          'audit',
          Logger::ERROR,
          "[{$this->getHttpRequest()->getRemoteAddress()}] ACCESS: Uzivatel #{$user->id} {$user->getIdentity()->username} zkusil pouzit funkciu vyzadujucu vyssiu rolu."
        );

        $response = $this->getHttpResponse();
        $response->setHeader('Cache-Control', 'no-cache');
        $response->setExpiration('1 sec');

        $this->getUser()->logout(true); // Odhlasenie spojene s odstranenim identity https://doc.nette.org/cs/3.1/access-control#toc-identita

        $this->flashRedirect(self::DEFAULT_SIGN_IN_PAGE, 'Na po??adovan?? akciu nem??te dostato??n?? opr??vnenie!', 'danger');
      }
      //Nastavenie jazyka podla u????vate??a 
      $this->language = $this->user_main->getUser($user->id)->lang->acronym;
    } else { //Neprihlaseny uzivatel
      if (!$user->isAllowed($this->name, $this->action)) { //Kontrola ACL
        if ($user->getLogoutReason() === Nette\Security\UserStorage::LOGOUT_INACTIVITY) {
          Logger::log(
            'webapp',
            Logger::ERROR,
            "[{$this->getHttpRequest()->getRemoteAddress()}] ACCESS: Uzivatel je neprihlaseny, jdeme na login."
          );

          // https://pla.nette.org/cs/jak-po-odeslani-formulare-zobrazit-stejnou-stranku
          $this->flashRedirect([self::DEFAULT_SIGN_IN_PAGE, ['backlink' => $this->storeRequest()]], 'Boli ste pr??li?? dlho neakt??vny a preto ste boli odhl??sen??! Pros??m, prihl??ste sa znovu.', 'danger');
        } else {
          Logger::log(
            'webapp',
            Logger::ERROR,
            "[{$this->getHttpRequest()->getRemoteAddress()}] ACCESS: Uzivatel je neprihlaseny a nem?? dostato??n?? opr??vnenie na dan?? oper??ciu({$this->name}:{$this->action})."
          );
          $this->getUser()->logout(true); // Odhlasenie spojene s odstranenim identity https://doc.nette.org/cs/3.1/access-control#toc-identita
          $this->flashRedirect(self::DEFAULT_SIGN_IN_PAGE, 'Nem??te dostato??n?? opr??vnenie na dan?? oper??ciu!', 'danger');
        }
      }
    }
    //Nastavenie textov podla jazyka 
    $this->texty_presentera->setLanguage($this->language);
  }

  public function actionOut(): void
  {
    $response = $this->getHttpResponse();
    $response->setHeader('Cache-Control', 'no-cache');
    $response->setExpiration('1 sec');

    if ($this->getUser()->getIdentity()) {
      Logger::log(
        'audit',
        Logger::INFO,
        "[{$this->getHttpRequest()->getRemoteAddress()}] Logout: odhlasen {$this->getUser()->getIdentity()->email}"
      );
    }
    $this->getUser()->logout(true); // Vyma??e aj identitu
    $this->flashRedirect(self::DEFAULT_SIGN_IN_PAGE, $this->texty_presentera->translate("base_log_out_mess"), "success");
  }

  public function beforeRender()
  {
    $this->template->setTranslator($this->texty_presentera);
  }

  /** Funkcia pre zjednodusenie vypisu flash spravy a presmerovania
   * @param array|string $redirect Adresa presmerovania
   * @param string $text Text pre vypis hlasenia
   * @param string $druh - druh hlasenia */
  public function flashRedirect($redirect, $text = "", $druh = "info")
  {
    $this->flashMessage($text, $druh);
    if (is_array($redirect)) {
      if (count($redirect) > 1) {
        if (!$this->isAjax()) {
          $this->redirect($redirect[0], $redirect[1]);
        } else {
          $this->redrawControl();
        }
      } elseif (count($redirect) == 1) {
        $this->redirect($redirect[0]);
      }
    } else {
      if (!$this->isAjax()) {
        $this->redirect($redirect);
      } else {
        $this->redrawControl();
      }
    }
  }

  /**
   * Funkcia pre zjednodusenie vypisu flash spravy a presmerovania aj pre chybovy stav
   * @param boolean $ok Podmienka
   * @param array|string $redirect Adresa presmerovania
   * @param string $textOk Text pre vypis hlasenia ak je podmienka splnena
   * @param string $textEr Text pre vypis hlasenia ak NIE je podmienka splnena  */
  public function flashOut($ok, $redirect, $textOk = "", $textEr = "")
  {
    if ($ok) {
      $this->flashRedirect($redirect, $textOk, "success");
    } else {
      $this->flashMessage($textEr, 'danger');
    }
  }

  /**
   * Uprava formulare pro Boostrap4
   */
  public function makeBootstrap4(Form $form): Form
  {
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = null;
    $renderer->wrappers['pair']['container'] = 'div class="form-group row"';
    $renderer->wrappers['pair']['.error'] = 'has-danger';
    $renderer->wrappers['control']['container'] = 'div class=col-sm-10';
    $renderer->wrappers['label']['container'] = 'div class="col-sm-2 col-form-label align-top text-right"';
    $renderer->wrappers['control']['description'] = 'span class="form-text font-italic"';
    $renderer->wrappers['control']['errorcontainer'] = 'span class=form-control-feedback';
    $renderer->wrappers['control']['.error'] = 'is-invalid';

    $renderer->wrappers['group']['container'] = null; // 'div class="col-sm-9 bg-light"';
    $renderer->wrappers['group']['label'] = 'span class="form-text font-weight-bold border-top p-1 mb-1"'; // bg-secondary text-white 

    foreach ($form->getControls() as $control) {
      $type = $control->getOption('type');
      if ($type === 'button' && !(isset($control->getControlPrototype()->attrs['class']) && strlen($control->getControlPrototype()->attrs['class']))) {
        $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-secondary');
        $usedPrimary = true;
      } elseif (in_array($type, ['text', 'textarea', 'select'], true)) {
        $control->getControlPrototype()->addClass('form-control');
      } elseif ($type === 'file') {
        $control->getControlPrototype()->addClass('form-control-file');
      } elseif (in_array($type, ['checkbox', 'radio'], true)) {
        if ($control instanceof Nette\Forms\Controls\Checkbox) {
          $control->getLabelPrototype()->addClass('form-check-label');
        } else {
          $control->getItemLabelPrototype()->addClass('form-check-label');
        }
        $control->getControlPrototype()->addClass('form-check-input');
        $control->getSeparatorPrototype()->setName('div')->addClass('form-check');
      }
    }
    $form->setTranslator($this->texty_presentera);
    return $form;
  }
}
