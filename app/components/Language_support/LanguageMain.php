<?php

namespace Language_support;

use App\Model;
use Nette;

/**
 * Hlavna trieda pre podporu jazykov pre presentre.
 *  
 * Môže spolupracovať s DB tabuľkou (meno tabuľky je ako parameter), v ktorej testuje existenciu daného jazyka.
 * Alebo je zoznam povolených jazykov uvedený v konfigurácii.
 * 
 * Posledna zmena(last change): 26.08.2021
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.2.2
 *
 * @property-read string $jazyk Skratka aktualneho jazyka
 * @property-read int $language_id Id aktualneho jazyka
 */
class LanguageMain implements Nette\Localization\Translator {
  use Nette\SmartObject;
  
  /** @var string Skratka jazyka */
  private $jazyk = 'sk';
  
  /** @var int Id jazyka */
  private $language_id = 1;

  /** @var array 
   * Pole prípustných jazykov v tvare ['id'=>'acronym'] */
	public $lang;
  
  /** @var array Pole konkretneho jazyka pre vystup */
  private $out_texty = [];
  
  public function __construct(?array $params, Nette\Database\Explorer $db) {
    if (isset($params['db_table'])) {
      $this->lang = $db->table($params['db_table'])->fetchPairs('id', 'acronym');
    } elseif (isset($params['allowed_langs'])) {
      $this->lang = $params['allowed_langs'];
    } elseif ($params == null) {
      $this->lang = [0=>'sk'];
    } else {
      throw new LanguageInvalidSettings("Invalid settings of language! Chybné nastavenie jazykov!", 1);
    }
  }

  /**
   * Pripojenie textov z neon súboru
   * @param string $file subor aj s cestou
   * @return $this */
  public function appendLanguageFile(string $file): LanguageMain { 
    $this->out_texty = array_merge($this->out_texty, Nette\Neon\Neon::decode(file_get_contents($file)));
    return $this;
  }
  
  /**
   * Nahradí existujúce texty novimi z neon súboru
   * @param string $file subor aj s cestou
   * @return $this */
  public function setNewLanguageFile(string $file): LanguageMain {
    $this->out_texty = Nette\Neon\Neon::decode(file_get_contents($file));
    return $this; 
  }

  /** 
   * Nastavenie aktualneho jazyka
   * @param string|int $language Skratka jazyka alebo jeho id 
   * @throws LanguageNotExist 
   * @return $this */
  public function setLanguage($language): LanguageMain {
    $lang_exist = is_numeric($language) ? array_key_exists($language, $this->lang) : in_array($language, $this->lang);
    
    if (!$lang_exist) {
      throw new LanguageNotExist("This language is not set! Požadovaný jazyk sa nenašiel!", 0);
    }
    
    // Nacitanie ID jazyka
    $this->language_id = is_numeric($language) ? $language : array_search($language, $this->lang);
    // Nacitanie skratky jazyka
    $this->jazyk = $this->lang[$this->language_id];
    
    // Nacitanie základných textov z neon suboru podla jazyka
    $this->out_texty = Nette\Neon\Neon::decode(file_get_contents(__DIR__ . '/lang_'.$this->jazyk.'.neon'));
    return $this;
  }

  /**
   * @return string */
  public function getJazyk():string {
    return $this->jazyk;
  }
  
  /**
   * Preklad kluca
   * @param mixed $message Kluc na preklad
   * @return string */
  public function translate(mixed $message, mixed ...$parameters): string {
    // https://doc.nette.org/cs/3.1/strings#toc-webalize
    $tmpm = Nette\Utils\Strings::webalize($message, '_', false); // Prevod z dôvodu validačných hlášok vo formulároch
    return array_key_exists($tmpm, $this->out_texty) ?  $this->out_texty[$tmpm] : $message;
  }
  
  /** 
   * Vrati id aktualne nastaveneho jazyka 
   * @return int */
  public function getLanguage_id(): int {
    return $this->language_id;
  }

  /**
   * Vrati pole textov ak je pod klucom definovane
   * @param string $key Nazov kluca 
   * @return array|null */
  public function getKeyArray(string $key): ?array {
    return is_array($this->out_texty[$key]) ? $this->out_texty[$key] : null;
  }

  /** Vráti pole textov pre konkrétny jazyk */
  public function getOutTexty(): array {
    return $this->out_texty;
  }
}
// Ak nebol pozadovany jazyk najdeny
class LanguageNotExist extends \Exception
{}
// Ak neboli najdene pozadovane konfiguracne nastavenia
class LanguageInvalidSettings extends \Exception
{}