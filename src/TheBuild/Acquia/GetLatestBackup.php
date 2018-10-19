<?php
/**
 * @file GetLatestBackup.php
 *
 * Get the latest backup of a site from the Acquia Cloud API.
 
 will use acquia.cloud.endpoint
 will use acquia.cloud.email and acquia.cloud.key if available
 *
 
 credentialsFile = ~/.acquia/cloudapi.conf
 email = acquia.cloud.email
 key = acquia.cloud.key
 endpoint = https://cloudapi.acquia.com/v1

 backupsFile = backups.json
 dir = artifacts/backups
 maxAge = 24
 
 realm =
 site =
 environment = 
 database = [site]
 propertyName =
 
 * @code
 *   <getlatestbackup />
 * @endcode
 *
 * @copyright 2018 Palantir.net, Inc.
 */

namespace TheBuild\Acquia;

use BuildException;
use PhingFile;


class GetLatestBackup extends AcquiaTask {

  protected $propertyName;
  protected $realm;
  protected $site;
  protected $env;
  protected $database;
  protected $dir;
  protected $maxAge = '24';
  /**
   * @var PhingFile $backupsFile
   */
  protected $backupsFile;

  protected $_required_keys = ['email', 'key', 'dir', 'realm', 'site', 'env'];

  /**
   * @var array $backups
   */
  protected $backups;


  public function setRealm($value) {
    $this->realm = $value;
  }
  public function setSite($value) {
    $this->site = $value;
  }
  public function setEnv($value) {
    $this->env = $value;
  }
  public function setDatabase($value) {
    $this->database = $value;
  }
  public function setBackupsFile($value) {
    $this->backupsFile = new PhingFile($value);
  }
  public function setDir($value) {
    $this->dir = new PhingFile($value);
  }
  public function setPropertyName($value) {
    $this->propertyName = new PhingFile($value);
  }

  public function main() {
    $this->loadCredentials();
    $this->validate();

    if (empty($this->backupsFile)) {
      $this->backupsFile = new PhingFile($this->dir, 'backups.json');
    }
    if (empty($this->database)) {
      $this->database = $this->site;
    }


    // do we have a current backup record?
    $latest = $this->getCurrentBackup();
    // if not, download a new backups.json
    if (!$latest) {
      // download new backups.json
      $this->fetchBackupRecords();
      $latest = $this->getCurrentBackup();
    }

    $time = new \DateTime('now');
    $time->setTimestamp($latest['started']);
    $this->log("The latest backup is from " . $time->format(DATE_RFC850));

    // does the current backup record exist on the filesystem?
    // if not, download the backup
    $filename = basename($latest['path']);
    $file = new PhingFile($this->dir, $filename);
    if (!$file->exists()) {
      $this->log("Downloading the latest backup to " . $file->getAbsolutePath());
      $this->downloadBackup($latest, $file);
    }
    else {
      $this->log("Latest backup already present at " . $file->getAbsolutePath());
    }

    if ($this->propertyName) {
      $project = $this->getProject();
      $project->setNewProperty($this->propertyName, $file->getAbsolutePath());
    }
  }

  protected function downloadBackup(array $backup, PhingFile $destination) {
    $stream = fopen($destination->getAbsolutePath(), 'wb');
    if (!$stream) {
      throw new BuildException('Can not write to ' . $destination->getAbsolutePath());
    }

    $request = $this->createRequest("/sites/{$this->realm}:{$this->site}/envs/{$this->env}/dbs/{$this->database}/backups/{$backup['id']}/download.json");
    $request->setConfig('store_body', FALSE);

    $observer = new \HTTP_Request2_Observer_UncompressingDownload($stream, 5000000000);
    $request->attach($observer);

    $response = $request->send();
    fclose($stream);

    $this->log("Downloaded " . $response->getHeader('content-length')/1000000 . "MB to " . $destination->getAbsolutePath());
  }

  /**
   * @return array|bool
   */
  protected function getCurrentBackup() {
    if ($this->backupsFile->exists()) {
      $backups = json_decode($this->backupsFile->contents(), TRUE);

      if (!isset($backups[0]['started'])) {
        return FALSE;
      }

      $newest = ['started' => 0];
      foreach ($backups as $b) {
        if ($b['started'] > $newest['started']) {
          $newest = $b;
        }
      }

      $backup_time = new \DateTime();
      $backup_time->setTimestamp($newest['started']);
      $threshold_time = new \DateTime("-{$this->maxAge} hours");

      if ($backup_time > $threshold_time) {
        return $newest;
      }
    }

    return FALSE;
  }

  protected function fetchBackupRecords() {
    $contents = $this->get("/sites/{$this->realm}:{$this->site}/envs/{$this->env}/dbs/{$this->database}/backups.json");
    $writer = new \FileWriter($this->backupsFile);
    $writer->write($contents);
  }

}
