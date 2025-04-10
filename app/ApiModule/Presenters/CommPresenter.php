<?php

namespace App\ApiModule\Presenters;

//use App\ApiModule\Model;
use App\Services;
use App\Services\Logger;
use Nette\Http;
use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * Prezenter pre komunikáciu api s perifériami.
 * Posledná zmena(last change): 07.04.2025
 *
 * Modul: API
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2025 - 2025 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
class CommPresenter extends BasePresenter
{

	/*public function actionData(): void
	{
		$this->sendJson($this->units->getUnits());
	}*/

	/** @var Services\MsgProcessor @inject */
	private $msgProcessor;

	/** @var Services\RaDataSource @inject */
	private $datasource;

	/**
	 * Formát data správy:
	 *      <session_id>\n
	 *      <data_payload_block>\n
	 *      
	 * Format data_payload_block:
	 *      <AES IV>:<AES_encrypted_data>
	 * oboje zapísané ako reťazec hexitu
	 * 
	 * Obsah AES_encrypted_data:
	 *      <CRC32 z dešifrovaného payloadu><dĺžka dát 2 byte><data><random padding>
	 *      
	 * Result:
	 *      200 - OK
	 *      403 - re-login, session invalid
	 *      400 - other error
	*/
	public function actionData()
	{
			Debugger::enable( Debugger::PRODUCTION );
			$logger = new Logger( 'pv-conn' );

			try {
					
					$httpRequest = $this->getHttpRequest();

					$remoteIp = $httpRequest->getRemoteAddress(); 
					$logger->setContext("D");

					$postSize = strlen( $httpRequest->getRawBody() );
					$logger->write( Logger::INFO, "data+ {$postSize}b {$remoteIp}");
					//D $logger->write( Logger::INFO, "[" . $httpRequest->getRawBody() ."]" );

					$msg_parts = explode ( "\n" , $httpRequest->getRawBody(), 3 );
					if( count($msg_parts)<2 ) {
							throw new \Exception("Bad request (1).");                
					}
					$session = Strings::trim($msg_parts[0]);
					$data = Strings::trim($msg_parts[1]);
					
					if( Strings::length( $session ) == 0  ) {
							throw new \Exception("Empty session ID.");
					} 
					
					$sessionData = explode( ":", $session, 3 );
					if( count($sessionData)<2 ) {
							throw new \Exception("Bad request (3).");                
					}
					$logger->write( Logger::INFO, "Session_id:{$sessionData[0]}"); 
					$sessionDevice = $this->datasource->checkSession( $sessionData[0], $sessionData[1] );
					$logger->setContext("D;D:{$sessionDevice->deviceId}");

					//D $logger->write( Logger::INFO,  $sessionDevice );
					
					$msgTotal = $this->decryptDataBlock( $data, $sessionDevice->sessionKey, $logger );

					//D/ $logger->write( Logger::INFO,  '  celok: ' . bin2hex($msgTotal) );
					$this->msgProcessor->process( $sessionDevice, $msgTotal, $remoteIp, $logger );  

					$logger->write( Logger::INFO, "OK");

					$this->template->result = "OK";
					
			} catch (\App\Exceptions\NoSessionException $e) { 

					$logger->write( Logger::ERROR,  "ERR: " . get_class($e) . ": " . $e->getMessage() );
					
					$httpResponse = $this->getHttpResponse();
					$httpResponse->setCode(Http\Response::S403_Forbidden );
					$httpResponse->setContentType('text/plain', 'UTF-8');
					$response = new \Nette\Application\Responses\TextResponse("ERR {$e->getMessage()}");
					$this->sendResponse($response);
					$this->terminate();

			} catch (\Exception $e) {
			
					//TODO: zapísať chybu do tabuľky chýb
			
					$logger->write( Logger::ERROR,  "ERR: " . get_class($e) . ": " . $e->getMessage() );
					
					$httpResponse = $this->getHttpResponse();
					$httpResponse->setCode(Http\Response::S400_BadRequest );
					$httpResponse->setContentType('text/plain', 'UTF-8');
					$response = new \Nette\Application\Responses\TextResponse("ERR {$e->getMessage()}");
					$this->sendResponse($response);
					$this->terminate();
			}
	}

	/**
	 * Formát data správy:
	 *      <session_id>;<SHA256 z payloadu>;<dátum a čas odoslania>;<dĺžka dát>;<data>
	 * Formát dát:
	 * 			<označenie senzora>:<hodnota>;<označenie senzora>:<hodnota>... - ak je viac posielaných hodnôt, tak sú oddelené ";"  
	 * Result:
	 *      200 - OK
	 *      403 - re-login, session invalid
	 *      400 - other error
	*/
	public function actionData_v0()
	{
			Debugger::enable( Debugger::PRODUCTION );
			$logger = new Logger( 'pv-conn' );

			try {
					
					$httpRequest = $this->getHttpRequest();

					$remoteIp = $httpRequest->getRemoteAddress(); 
					$logger->setContext("D");

					$postSize = strlen( $httpRequest->getRawBody() );
					$logger->write( Logger::INFO, "data+ {$postSize}b {$remoteIp}");
					//D $logger->write( Logger::INFO, "[" . $httpRequest->getRawBody() ."]" );

					$msg_parts = explode ( ";" , $httpRequest->getRawBody(), 4 );
					/*
					$msg_parts[0] - session_id
					$msg_parts[1] - SHA256 z payloadu
					$msg_parts[2] - dátum a čas odoslania
					$msg_parts[3] - dĺžka dát
					$msg_parts[4] - data
					*/
					if( count($msg_parts) < 5 ) {
							throw new \Exception("Bad request (1). Message is too short!");                
					}
					$session = Strings::trim($msg_parts[0]);
					$data = array_shift($msg_parts); // Vypustí prvý prvok poľa
					
					if( Strings::length( $session ) == 0  ) {
							throw new \Exception("Empty session ID.");
					} 
					
					$sessionData = explode( ":", $session, 3 );
					// $sessionData[0] - sessionId
					// $sessionData[1] - sessionHash
					if( count($sessionData)<2 ) {
							throw new \Exception("Bad request (3).");                
					}
					$logger->write( Logger::INFO, "Session_id:{$sessionData[0]}"); 
					$sessionDevice = $this->datasource->checkSession( $sessionData[0], $sessionData[1] );
					$logger->setContext("D;D:{$sessionDevice->deviceId}");

					//D $logger->write( Logger::INFO,  $sessionDevice );
					
					//$msgTotal = $this->decryptDataBlock( $data, $sessionDevice->sessionKey, $logger );
					//old - <CRC32 z dešifrovaného payloadu><dĺžka dát 2 byte><data><random padding>
					//new - <SHA256 z payloadu>;<dátum a čas odoslania>;<dĺžka dát>;<data>
					//D/ $logger->write( Logger::INFO,  '  celok: ' . bin2hex($msgTotal) );
					$this->msgProcessor->process_v0( $sessionDevice, $data, $remoteIp, $logger );  

					$logger->write( Logger::INFO, "OK");

					$this->template->result = "OK";
					
			} catch (\App\Exceptions\NoSessionException $e) { 

					$logger->write( Logger::ERROR,  "ERR: " . get_class($e) . ": " . $e->getMessage() );
					
					$httpResponse = $this->getHttpResponse();
					$httpResponse->setCode(Http\Response::S403_Forbidden );
					$httpResponse->setContentType('text/plain', 'UTF-8');
					$response = new \Nette\Application\Responses\TextResponse("ERR {$e->getMessage()}");
					$this->sendResponse($response);
					$this->terminate();

			} catch (\Exception $e) {
			
					//TODO: zapísať chybu do tabuľky chýb
			
					$logger->write( Logger::ERROR,  "ERR: " . get_class($e) . ": " . $e->getMessage() );
					
					$httpResponse = $this->getHttpResponse();
					$httpResponse->setCode(Http\Response::S400_BadRequest );
					$httpResponse->setContentType('text/plain', 'UTF-8');
					$response = new \Nette\Application\Responses\TextResponse("ERR {$e->getMessage()}");
					$this->sendResponse($response);
					$this->terminate();
			}
	}

}
