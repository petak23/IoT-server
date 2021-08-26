<?php

declare(strict_types=1);

namespace App\Services;

use Nette;

/**
 * Odoslanie e-mailov
 * Last change 26.08.2021
 * 
 * @github     Forked from petrbrouzda/RatatoskrIoT
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class MailService {
    use Nette\SmartObject;
    
    public $mailFrom;
    public $mailAdmin;

	public function __construct($mailFrom,
                              $mailAdmin
                              ) {
    $this->mailFrom = $mailFrom;
    $this->mailAdmin = $mailAdmin;
  }

  public function sendMailAdmin( $subject, $text ): void {
    $this->sendMail(  $this->mailAdmin,
                      $subject,
                      $text
    );
  }

  public function sendMail( $to, $subject, $text ) {
    $mail = new Nette\Mail\Message;
    $mail->setFrom( $this->mailFrom )
        ->addTo($to)
        ->setSubject( "IoT-server: {$subject}")
        ->setHtmlBody($text);
    try {
      $sendmail = new Nette\Mail\SendmailMailer;
      $sendmail->send($mail);
    } catch (Exception $e) {
      throw new SendException('Došlo k chybe pri odosielaní e-mailu. Skúste neskôr znovu...'.$e->getMessage());
    }
  }
}