<?php
/**
 * @file IncludeResourceTask.php
 *
 * @copyright 2016 Palantir.net, Inc.
 */

namespace TheBuild;

use PhingFile;
use BuildException;
use GuzzleHttp\Client as GuzzleHttpClient;


class AcquiaCloudDatabaseTask extends \Task {

  /**
   * Configurable: location of the Acquia Cloud configuration file.
   * @var PhingFile
   */
  protected $acquiaCloudConf;

  /**
   * Configurable: the Acquia Cloud "realm", generally either "prod" or "devcloud".
   * @var string
   */
  protected $acquiaRealm;

  /**
   * Configurable: the Acquia site name.
   * @var string
   */
  protected $acquiaSite;

  /**
   * Configurable: the Acquia environment to download backups from.
   * @var string
   */
  protected $acquiaEnv = 'dev';

  /**
   * Configurable: the directory to download the latest backup to.
   * @var PhingFile
   */
  protected $dest;

  /**
   * Configurable: allow re-downloading a backup.
   * @var bool
   */
  protected $overwrite = FALSE;

  /**
   * Configurable: a property name to use for the path to the downloaded backup.
   * @var string
   */
  protected $resultProperty;

  /**
   * Acquia Cloud configuration loaded from the $acquiaCloudConf.
   * @var \stdClass
   */
  protected $conf;

  /**
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Init tasks.
   *
   * No user values are available at this point.
   */
  public function init() {}


  /**
   * Download the most recent database backup.
   */
  public function main() {
    $this->validate();
    $this->configure();

    $backups = $this->getAvailableBackups();
    $latest = $this->selectMostRecent($backups);
    $file = $this->downloadBackup($latest);

    if ($this->resultProperty) {
      $this->getProject()->setNewProperty($this->resultProperty, $file->getAbsolutePath());
    }
  }

  public function configure() {
    $this->conf = \GuzzleHttp\json_decode($this->acquiaCloudConf->contents());
    if (empty($this->conf->email) || empty($this->conf->key)) {
      throw new BuildException(sprintf("Email or key not found in Acquia Cloud conf file at '%s'", $this->acquiaCloudConf->getPath()));
    }

    if (empty($this->conf->endpoint)) {
      $this->conf->endpoint = 'https://cloudapi.acquia.com/v1/';
    }

    $this->client = new GuzzleHttpClient([
      'base_uri' => $this->conf->endpoint,
      'auth' => [$this->conf->email, $this->conf->key],
    ]);
  }


  /**
   * Verify required attributes (?).
   */
  public function validate() {
    $errors = [];

    if (!(isset($this->acquiaCloudConf) && $this->acquiaCloudConf->exists() && $this->acquiaCloudConf->isFile() && $this->acquiaCloudConf->canRead())) {
      $errors[] = sprintf("Can't read Acquia Cloud conf file at '%s'", $this->acquiaCloudConf->getPath());
    }

    if (empty($this->acquiaRealm)) {
      $errors[] = "The 'acquiaRealm' attribute must be set; this depends on the Acquia account type and is generally 'devcloud' or 'prod'.";
    }

    if (empty($this->acquiaSite)) {
      $errors[] = "The 'acquiaSite' attribute must be set to the name of your Acquia site.";
    }

    if (!in_array($this->acquiaEnv, ['dev', 'test', 'prod'])) {
      $errors[] = "The 'acquiaEnv' attribute must be either 'dev', 'test', or 'prod'.";
    }

    if (empty($this->dest) || !$this->dest->isDirectory()) {
      $errors[] = "The 'dest' attribute must be set to a directory.";
    }

    if (!empty($errors)) {
      $msg = sprintf("%s attribute problems: \r\n * %s", count($errors), implode("\r\n * ", $errors));
      throw new BuildException($msg);
    }
  }

  public function getAvailableBackups() {
    $path = "sites/{$this->acquiaRealm}:{$this->acquiaSite}/envs/{$this->acquiaEnv}/dbs/{$this->acquiaSite}/backups.json";

    try {
      $response = $this->client->get($path);
    }
    catch (\Exception $e) {
      // This makes the output shorter/less verbose. If that's not a good thing, this should be removed.
      throw new BuildException($e->getMessage());
    }

    $backups = \GuzzleHttp\json_decode($response->getBody());
    if (empty($backups)) {
      throw new BuildException('No backups found.');
    }

    return $backups;
  }

  public function selectMostRecent($backups) {
    usort($backups, function($a, $b) {
      if ($a->started == $b->started) { return 0; }
      return ($a->started < $b->started ? -1 : 1);
    });

    return end($backups);
  }

  /**
   * @param \stdClass $backup
   *
   * @return \PhingFile
   * @throws \IOException
   */
  public function downloadBackup($backup) {
    $file = new PhingFile($this->dest, basename($backup->path));
    $file->getParentFile()->mkdirs();

    if ($this->overwrite && $file->exists()) {
      // Remove existing backup.
      $this->log("Deleting existing backup '" . $file->getPath() . "'");

      if ($file->delete(TRUE) === FALSE) {
        throw new BuildException("Failed to delete existing backup '" . $file->getPath() . "'");
      }
    }

    if (!$file->exists()) {
      $this->log("Downloading {$backup->path}");
      $response = $this->client->get($backup->link);
      $bytes = file_put_contents($file->getAbsolutePath(), $response->getBody());

      if ($bytes === FALSE) {
        throw new BuildException("Failed to download backup to " . $file->getPath());
      }

      $this->log(sprintf('%s bytes written to %s', $bytes, $file->getPath()));
    }

    return $file;
  }

  /******
   * Setters for Phing attributes.
   ******/

  public function setAcquiaCloudConf(PhingFile $conf) {
    $this->acquiaCloudConf = $conf;
  }

  public function setAcquiaRealm($val) {
    $this->acquiaRealm = $val;
  }

  public function setAcquiaSite($val) {
    $this->acquiaSite = $val;
  }

  public function setAcquiaEnv($val) {
    $this->acquiaEnv = $val;
  }

  public function setDest(PhingFile $dest) {
    $this->dest = $dest;
  }

  public function setOverwrite($val) {
    $this->overwrite = $val;
  }

  public function setResultProperty($val) {
    $this->resultProperty = $val;
  }

}
