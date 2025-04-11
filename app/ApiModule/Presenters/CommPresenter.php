<?php

namespace App\ApiModule\Presenters;

//use App\ApiModule\Model;
use App\Services;
use App\Services\Logger;
use Nette\Http;
use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * Presenter pre komunikáciu api s perifériami.
 * Posledná zmena(last change): 11.04.2025
 *
 * Modul: API
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2025 - 2025 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.1
 */
class CommPresenter extends BasePresenter
{
	/** @var Services\MsgProcessor @inject */
	private $msgProcessor;

	/**
	 * Formát data správy:
	 *      <SHA256 z payloadu>;<dátum a čas odoslania>;<meno zariadenia>;<dĺžka dát>;<data>
	 * Formát dát: (označenie senzora je jedinečná hodnota)
	 * 			<označenie senzora>:<hodnota>;<označenie senzora>:<hodnota>... - ak je viac posielaných hodnôt, tak sú oddelené ";"  
	 * Result:
	 *      200 - OK
	 *      400 - other error
	*/
	public function actionData_pv()
	{
			Debugger::enable( Debugger::PRODUCTION );
			$logger = new Logger( 'pv-conn' );

			try {
					
					$httpRequest = $this->getHttpRequest();

					$remoteIp = $httpRequest->getRemoteAddress(); 
					$logger->setContext("D");

					$postSize = strlen( $httpRequest->getRawBody() );
					$logger->write( Logger::INFO, "data+ {$postSize}b {$remoteIp}");
					$logger->write( Logger::INFO, "[" . $httpRequest->getRawBody() ."]" );

					$msg_parts = explode ( ";" , $httpRequest->getRawBody(), 4 );
					/*
					$msg_parts[0] - SHA256 z payloadu
					$msg_parts[1] - dátum a čas odoslania 
					$msg_parts[2] - meno zariadenia
					$msg_parts[3] - dĺžka dát
					$msg_parts[4] - data
					*/
					if( count($msg_parts) < 5 ) {
						throw new \Exception("Bad request (1). Message is too short!");                
					}
					
					// TODO - kontrola správy na sha256 + vloženie hash hesla z údajov
					$control_hash = hash('sha256', $msg_parts[1] . $msg_parts[2] . $msg_parts[3] . $msg_parts[4] ."taJne687*+WX_-heslo"); //
					if( $control_hash !== $msg_parts[0]  ) {
						throw new \Exception("Not valid sha256 of message!");
					}

					if( strlen($msg_parts[4]) !== (int)$msg_parts[3]  ) {
						throw new \Exception("Incorrect data length!");
					}
					
					$data = array_shift($msg_parts); // Vypustí prvý prvok poľa teda <SHA256 z payloadu>

					// Formát data: array [<dátum a čas odoslania>, <meno zariadenia>, <dĺžka dát>, <data>]
					$this->msgProcessor->process_pv( $data, $remoteIp, $logger );  

					$logger->write( Logger::INFO, "OK");

					$this->sendJson(['status' => 200, 'message' => 'OK']);
					
			} catch (\Exception $e) {
			
					//TODO: zapísať chybu do tabuľky chýb
			
					$logger->write( Logger::ERROR,  "ERR: " . get_class($e) . ": " . $e->getMessage() );
					
					$httpResponse = $this->getHttpResponse();
					$httpResponse->setCode(Http\Response::S400_BadRequest );
					$this->sendJson(['status' => 400, 'message' => "ERR {$e->getMessage()}"]);
					$this->terminate();
			}
	}

}
