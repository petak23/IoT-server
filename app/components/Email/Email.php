<?php

declare(strict_types=1);

namespace PeterVojtech\Email;

use DbTable;
use Latte;
use Nette;

/**
 * Komponenta pre zjedndusenie odoslania emailu
 * Posledna zmena(last change): 04.01.2023
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.1.3
 */

class EmailControl extends Nette\Application\UI\Control
{

  /** @var Nette\Mail\Message */
  private $mail;
  /** @var DbTable\User_main */
  public $user_main;

  /** @var string */
  private $email_list;
  /** @var string */
  private $template;
  /** @var string */
  private $from;

  /** @var string */
  private $admin_email;
  /** @var string */
  private $site_name;
  /** @var object */
  private $host_url;

  public function __construct(
    string $admin_email,
    DbTable\User_main $user_main,
    Nette\Http\Request $http_request
  ) {
    $this->admin_email = $admin_email;
    $this->from = $admin_email;
    $this->mail = new Nette\Mail\Message;
    $this->user_main = $user_main;
    $this->host_url = $http_request->getUrl();
    $this->site_name = $this->host_url->host . $this->host_url->scriptPath; // Nazov stranky v tvare www.nieco.sk
    $this->site_name = substr($this->site_name, 0, strlen($this->site_name) - 1);
  }

  /** 
   * @param string $template Kompletná cesta k súboru template
   * @param int $from Id uzivatela od koho
   * @param int $id_user_roles Minimalna uroven registracie */
  public function nastav(string $template, int $from, int $id_user_roles): EmailControl
  {
    $this->template = $template;
    $this->from = $this->user_main->find($from)->email;
    $this->email_list = $this->user_main->emailUsersListStr($id_user_roles);
    foreach (explode(",", $this->email_list) as $c) {
      $this->mail->addTo(trim($c));
    }
    return $this;
  }

  /** Funkcia pre odoslanie emailu
   * Vracia zoznam komu bol odoslany email
   * @param array $params Parametre správy
   * @param string $subjekt Subjekt emailu
   * @throws SendException
   */
  public function send($params, $subjekt, $text = null): string
  {
    $templ = new Latte\Engine;
    $html_body = $this->template != null ? $templ->renderToString($this->template, $params) : ($text != null ? $text : "");
    $this->mail->setFrom($this->site_name . ' <' .  $this->from . '>');
    $this->mail->setSubject($this->site_name . ": " . $subjekt)
      ->setHtmlBody($html_body);
    try {
      if ($this->host_url->host != "localhost") {
        $sendmail = new Nette\Mail\SendmailMailer;
        $sendmail->send($this->mail);
      }
      return $this->email_list;
    } catch (Nette\Mail\SendException $e) {
      throw new SendException('Došlo k chybe pri odosielaní e-mailu. Skúste neskôr znovu...' . $e->getMessage());
    }
  }

  /** 
   * Funkcia pre odoslanie emailu
   * @param int $from Id uzivatela od koho 
   * @param string $to Email prijemcu
   * @param string $subjekt Subjekt emailu
   * @param string $text Text správy
   * @param array $params Parametre správy
   * @param string $template Kompletná cesta k súboru template
   * @throws SendException */
  public function sendMail(int $from, string $to, string $subjekt, ?string $text = null, array $params = [], ?string $template = null): void
  {
    $_templ = new Latte\Engine;
    // Vytvor telo správy
    $html_body = $template != null ? $_templ->renderToString($template, $params) : ($text != null ? $text : "");

    $mail = new Nette\Mail\Message;
    $mail->setFrom($this->site_name . ' <' .  $this->user_main->find($from)->email . '>')
      ->addTo($to)
      ->setSubject($this->site_name . ": " . $subjekt)
      ->setHtmlBody($html_body);
    try {
      if ($this->host_url->host != "localhost") {
        $sendmail = new Nette\Mail\SendmailMailer;
        $sendmail->send($mail);
      }
    } catch (Nette\Mail\SendException $e) {
      throw new SendException($e->getMessage());
    }
  }

  /** 
   * Funkcia pre odoslanie administračného emailu
   *
   * @param string $subjekt Subjekt emailu
   * @param string $text Text správy
   * @throws SendException */
  public function sendAdminMail(string $subject, string $text): void
  {
    $mail = new Nette\Mail\Message;
    $mail->setFrom($this->site_name . ' <' .  $this->from . '>')
      ->addTo($this->admin_email)
      ->setSubject($this->site_name . ": " . $subject)
      ->setHtmlBody($text);
    try {
      if ($this->host_url->host != "localhost") {
        $sendmail = new Nette\Mail\SendmailMailer;
        $sendmail->send($mail);
      }
    } catch (Nette\Mail\SendException $e) {
      throw new SendException('Došlo k chybe pri odosielaní e-mailu. Skúste neskôr znovu...' . $e->getMessage());
    }
  }
}

class SendException extends Nette\Mail\SendException
{
}
