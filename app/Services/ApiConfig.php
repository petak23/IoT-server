<?php

declare(strict_types=1);

namespace App\Services;

use Nette;

class ApiConfig
{
	use Nette\SmartObject;

	private $configs = [];

	private $masterPassword;

	public function __construct(
		$masterPassword,
		$links,
		$appName,
		$dataRetentionDays,
		$minYear
	) {
		$this->masterPassword = $masterPassword;
		$this->configs = [
			"links" => $links,
			"appName" => $appName,
			"dataRetentionDays" => $dataRetentionDays,
			"minYear" => $minYear,
		];
	}

	public function getConfigs()
	{
		return $this->configs;
	}

	private function getMasterKey()
	{
		return hash("sha256", $this->masterPassword . 'RatatoskrIoT', true);
	}

	public function encrypt($data, $fieldName)
	{
		$aesIV = substr(hash("sha256", $fieldName, true), 0, 16);
		$aesKey = $this->getMasterKey();
		$encrypted = openssl_encrypt($data, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $aesIV);
		if ($encrypted === FALSE) {
			Logger::log('webapp', Logger::ERROR, "nelze zasifrovat");
		}
		return bin2hex($encrypted);
	}

	public function decrypt($data, $fieldName)
	{
		$aesIV = substr(hash("sha256", $fieldName, true), 0, 16);
		$aesKey = $this->getMasterKey();

		$decrypted = openssl_decrypt(hex2bin($data), 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $aesIV);
		if ($decrypted == FALSE) {
			Logger::log('webapp', Logger::ERROR, "nelze desifrovat");
			return "";
		}
		return $decrypted;
	}
}
