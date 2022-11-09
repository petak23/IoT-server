<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\Device;
use App\Model;
use Nette;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Nette\Utils\FileSystem;
use Nette\Application\UI\Form;
use Nette\Http\Url;
use Nette\Application\Responses\FileResponse;

use App\Services\Logger;

/**
 * Presenter pre prácu so zariadenimi
 * Posledna zmena 29.07.2022
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2022 - 2022 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
final class DevicePresenter extends BaseAdminPresenter
{
  use Nette\SmartObject;

  private $id_device = 0;

  /** @persistent */
  public $viewid = "";

  /** @var \App\Services\Config */
  protected $config;

  // Database tables
  /** @var Model\PV_Blobs @inject */
  public $blobs;
  /** @var Model\PV_Devices @inject */
  public $devices;
  /** @var Model\PV_Sensors @inject */
  public $sensors;
  /** @var Model\PV_Sessions @inject */
  public $sessions;
  /** @var Model\PV_Updates @inject */
  public $updates;

  // Forms
  /** @var Device\DeviceFormFactory @inject*/
  public $deviceForm;

  public function __construct(\App\Services\Config $config)
  {
    $this->config = $config;
    $this->links = $config->links;
    $this->appName = $config->appName;
  }

  /**
   * Funkcia pre nájdenie zariadenia a overenie oprávnenia
   */
  private function getDevice(int $id = 0, bool $to_array = false, bool $checkAcces = true): Nette\Database\Table\ActiveRow|array|null
  {
    $this->id_device = $id;
    $device = $this->devices->getDevice($id);
    if (!$device) {
      $this->setView('notFound');
      return null;
    }
    if ($checkAcces) $this->checkAcces($device->user_id);

    return ($to_array) ? $device->toArray() : $device;
  }

  private function setSubmenu(int $id, string $name, int $blobCount)
  {
    // 2 - Položka Device:... v hlavnom menu
    $this['menu']->addNode(
      2,
      [
        'name' => "· " . $this->texty_presentera->translate('device_h1') . " {$name}",
        'link' => $this->link("Device:show", $id),
        'id'   => $id
      ]
    );
    if ($blobCount > 0) {
      $this['menu']->addNode(2, [
        'name' => "· · " . $this->texty_presentera->translate('device_files_h3') . " ({$blobCount})",
        'link' => $this->link("Device:blobs", $id),
        'id'   => $id
      ]);
    }
  }

  /**
   * Zoznam zariadení
   * @addr {server}/device/list/ */
  public function renderList()
  {
    $this->template->devices = $this->devices->getDevicesUser($this->getUser()->id);
  }


  //public function renderCreate() {}

  public function actionEdit(int $id): void
  {
    $post = $this->getDevice($id, true);
    Logger::log(
      'audit',
      Logger::INFO,
      sprintf($this->texty_presentera->translate('log_device_edit'), $this->getUser()->id, $this->getUser()->getIdentity()->email, $id)
    );

    $this->template->name = $post['name'];
    $this->template->id = $id;

    $arr = Strings::split($post['name'], '~:~');
    $post['name'] = $arr[1];
    $post['passphrase'] = $this->config->decrypt($post['passphrase'], $post['name']);

    $this['deviceForm']->setDefaults($post);
  }

  protected function createComponentDeviceForm(): Form
  {
    $form = $this->deviceForm->create($this->language, $this->id_device);
    $form->onSuccess[] = function () {
      $this->flashRedirect($this->id_device ? ["Device:show", $this->id_device] : "Device:list", $this->texty_presentera->translate('base_save_ok'), 'success');
    };
    return $this->makeBootstrap4($form);
  }

  //----------------------------------------------------------------------

  private function secondsToTime($inputSeconds)
  {
    $secondsInAMinute = 60;
    $secondsInAnHour = 3600;
    $secondsInADay = 86400;

    // Extract days
    $days = floor($inputSeconds / $secondsInADay);

    // Extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // Extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // Extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // Format and return
    $timeParts = [];
    $sections = [
      'd' => (int)$days,
      'hod' => (int)$hours,
      'min' => (int)$minutes,
      'sec' => (int)$seconds,
    ];

    foreach ($sections as $name => $value) {
      if ($value > 0) {
        $timeParts[] = $value . ' ' . $name;
      }
    }

    return implode(', ', $timeParts);
  }


  public function renderShow(int $id): void
  {

    $post =  $this->getDevice($id, true, false);

    $post['passphrase'] = $this->config->decrypt($post['passphrase'], $post['name']);
    $this->checkAcces($post['user_id']);
    if ($post['uptime']) {
      $this->template->uptime = $this->secondsToTime($post['uptime']);
    }

    $lastLoginTs = (DateTime::from($post['last_login']))->getTimestamp();
    $post['problem_mark'] = false;
    if ($post['last_bad_login'] != NULL) {
      if ($post['last_login'] != NULL) {
        $lastErrLoginTs = (DateTime::from($post['last_bad_login']))->getTimestamp();
        if ($lastErrLoginTs >  $lastLoginTs) {
          $post['problem_mark'] = true;
        }
      } else {
        $post['problem_mark'] = true;
      }
    }

    $url = new Url($this->getHttpRequest()->getUrl()->getBaseUrl());
    $url->setScheme('http');
    $url1 = $url->getAbsoluteUrl() . 'ra';

    $this->template->url = substr($url1, 7);
    $this->template->device = $post;
    $this->template->soubory = $this->blobs->getBlobCount($id);
    $this->template->sensors = $this->sensors->getDeviceSensors($id, $post['monitoring']);
    $this->setSubmenu($id, $post['name'], $this->template->soubory);

    $lastTime = $lastLoginTs;

    foreach ($this->template->sensors as $sensor) {
      if ($sensor->last_data_time) {
        $utime = (DateTime::from($sensor->last_data_time))->getTimestamp();
        if (!$lastTime || ($utime > $lastTime)) {
          $lastTime = $utime;
        }
      }
    }

    $this->template->lastComm = $lastTime;
    $this->template->updates = $this->updates->getOtaUpdates($id);
    $this->template->jsonUrl = $this->link('//Json:data', ['token' => $post['json_token'], 'id' => $post['id']]);
    $this->template->jsonUrl2 = $this->link('//Json:meteo', ['token' => $post['json_token'], 'id' => $post['id'], 'temp' => 'JMENO_TEMP_SENZORU', 'rain' => 'JMENO_RAIN_SENZORU']);
    $this->template->blobUrl = $this->link('//Gallery:show', ['token' => $post['blob_token'], 'id' => $post['id']]);
  }


  public function renderBlobs(int $id): void
  {
    $this->template->device = $this->getDevice($id);
    $this->template->blobs = $this->blobs->getBlobs($id);
    $this->setSubmenu($id, $this->template->device['name'], $this->blobs->getBlobCount($id));
  }


  public function renderDownload(int $id, int $blobId): void
  {
    $this->getDevice($id, true);

    $blob = $this->blobs->getBlob($id, $blobId);
    if (!$blob) {
      $this->error('Soubor nenalezen nebo k němu nejsou práva.');
    }

    $fileName = $blob->data_time->format('Ymd_His') . "_" . $id . "_" .
      Strings::webalize($blob['description'], '._') . "." . $blob->extension;

    $contentType = $blob->extension == 'csv' ? 'text/csv' : 'application/octet-stream';
    if ($blob->extension == 'jpg') {
      $contentType = 'image/jpeg';
    }

    $file = __DIR__ . "/../../data/" . $blob['filename'];
    //$file = "data/" . $blob['filename']; // Ak sa podadresár data presunie do www 
    $response = new FileResponse($file, $fileName, $contentType);
    $this->sendResponse($response);
  }


  //----------------------------------------------------------------------


  public function actionDelete(int $id): void
  {
    $this->template->device = $this->getDevice($id);
    $this->template->statMeasures = $this->sensors->getDataStatsMeasures($id);
    $this->template->statSumdata = $this->sensors->getDataStatsSumdata($id);
  }

  protected function createComponentDeleteForm(): Form
  {
    $form = new Form;
    $form->addProtection();

    $form->addCheckbox('potvrdit', 'device_form_del')
      ->setOption('description', 'device_form_del_d')
      ->setRequired();

    $form->addSubmit('delete', 'device_delete')
      ->setHtmlAttribute('class', 'btn btn-danger')
      ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    $form->onSuccess[] = [$this, 'deleteFormSucceeded'];

    $this->makeBootstrap4($form);
    return $form;
  }

  public function deleteFormSucceeded(Form $form, array $values): void
  {
    if ($this->id_device) {
      //overenie opravnenia
      $this->getDevice($this->id_device);

      Logger::log('audit', Logger::INFO, "[{$this->getHttpRequest()->getRemoteAddress()}, {$this->getUser()->getIdentity()->username}] Mazu zarizeni {$this->id_device}");
      $this->devices->deleteDevice($this->id_device);
    }

    $this->flashRedirect("Device:list", $this->texty_presentera->translate('device_form_del_ok'), 'success');
  }

  //----------------------------------------------------------------------



  public function actionSendconfig(int $id): void
  {

    $post = $this->getDevice($id, true);

    $this->template->name = $post['name'];
    $this->template->id = $id;

    $arr = Strings::split($post['name'], '~:~');
    $post['name'] = $arr[1];

    $this['sendconfigForm']->setDefaults($post);
  }

  protected function createComponentSendconfigForm(): Form
  {
    $form = new Form;
    $form->addProtection();

    $form->addTextArea('config_data', 'Změna konfigurace:')
      ->setHtmlAttribute('rows', 4)
      ->setHtmlAttribute('cols', 50)
      ->setRequired();

    $form->addSubmit('send', 'Uložit')
      ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    $form->onSuccess[] = [$this, 'sendconfigFormSucceeded'];

    $this->makeBootstrap4($form);
    return $form;
  }


  public function sendconfigFormSucceeded(Form $form, array $values): void
  {
    if ($this->id_device) {
      // editace
      $device = $this->getDevice($this->id_device);

      if (!$device->config_ver) {
        $values['config_ver'] = '1';
      } else {
        $values['config_ver'] = intval($device->config_ver) + 1;
      }
      $device->update($values);
      $this->sessions->deleteSession($this->id_device);
      $this->flashRedirect(["Device:show", $this->id_device], "Změny provedeny.", 'success');
    } else {
      // zalozeni - to se nema stat
      $this->redirect("Device:list");
    }
  }


  //----------------------------------------------------------------------



  public function actionUpdate(int $id): void
  {
    $post = $this->getDevice($id, true);

    $this->template->id = $id;
    $this->template->name = $post['name'];

    $arr = Strings::split($post['name'], '~:~');
    $post['name'] = $arr[1];

    $warningOTA = true;
    $warningVer = true;

    // rozebrat $post['app_name'] a udelat z nej post['fromVersion']
    if (substr($post['app_name'], 0, 1) == '[') {
      $pos = strpos($post['app_name'], ']');
      if ($pos !== false) {
        $post['fromVersion'] = substr($post['app_name'], 1, $pos - 1);
        $warningVer = false;
      }
    }
    // pokud v $post['app_name'] neni 'OTA Y', dat varovani, ze aplikace to asi nepodporuje
    if (false !== strpos($post['app_name'], "; OTA Y")) {
      $warningOTA = false;
    }
    $this->template->warningOTA = $warningOTA;
    $this->template->warningVer = $warningVer;

    $this['updateForm']->setDefaults($post);
  }

  protected function createComponentUpdateForm(): Form
  {
    $form = new Form;
    $form->addProtection();

    $form->addText('fromVersion', 'ID verze aplikace, ze které se aktualizuje:')
      ->setRequired()
      ->addRule(Form::PATTERN, 'Jen písmena, čísla a znaky .-_', '([0-9A-Za-z_\.\-]+)')
      ->setOption('description', 'ID stávající verze aplikace; je uvedeno v hranatých závorkách na začátku jména aplikace.')
      ->setHtmlAttribute('size', 50);

    $form->addUpload('image', 'Soubor s aktualizací:')
      ->setRequired();

    $form->addSubmit('send', 'Uložit')
      ->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.form.submit(); this.disabled=true; } return false;');

    $form->onSuccess[] = [$this, 'updateFormSucceeded'];

    $this->makeBootstrap4($form);
    return $form;
  }


  private function getUpdateFilename($deviceId, $updateId)
  {
    return __DIR__ . "/../../data/ota/{$deviceId}_{$updateId}.bin";
  }

  public function updateFormSucceeded(Form $form, array $values): void
  {

    if ($this->id_device) {
      // editace
      $this->getDevice($this->id_device);

      $file = $values['image'];
      // kontrola, jestli se nahrál dobře
      if (!$file->isOk()) {
        $this->flashRedirect(['Device:show', $this->id_device], 'Něco selhalo.', 'danger');
        return;
      }

      // vraci hexastream
      $fileHash = hash("sha256", $file->getContents(), false);

      // ulozit data do tabulky a soubor na disk
      $fileId = $this->updates->otaUpdateCreate($this->id_device, $values['fromVersion'], $fileHash);
      if ($fileId == -1) {
        $this->flashRedirect(['Device:show', $this->id_device], 'Pro tohle zařízení a verzi aplikace již požadavek na update existuje.', 'danger');
        return;
      }

      // přesunutí souboru z temp složky někam, kam nahráváš soubory
      $file->move($this->getUpdateFilename($this->id_device, $fileId));

      Logger::log(
        'webapp',
        Logger::INFO,
        "Uzivatel #{$this->getUser()->id} {$this->getUser()->getIdentity()->username} posila aktualizaci {$fileId} na zarizeni {$this->id_device}"
      );

      $this->sessions->deleteSession($this->id_device);

      $this->flashRedirect(['Device:show', $this->id_device], "Aktualizace připravena.", 'success');
    } else {
      // to se nema stat
      $this->redirect("Device:list");
    }
  }

  public function renderDeleteupdate(int $device_id, int $update_id): void
  {
    $this->getDevice($device_id);

    $this->updates->otaDeleteUpdate($device_id, $update_id);

    FileSystem::delete($this->getUpdateFilename($device_id, $update_id));

    Logger::log(
      'webapp',
      Logger::INFO,
      "Uzivatel #{$this->getUser()->id} {$this->getUser()->getIdentity()->username} maze aktualizaci {$update_id} na zarizeni {$device_id}"
    );

    $this->flashRedirect(['Device:show', $this->id_device], "Aktualizace smazána.", 'success');
  }
}
