<?php
/**
 * @file IncludeResourceTask.php
 *
 * @copyright 2016 Palantir.net, Inc.
 */

namespace TheBuild;

use PhingFile;
use BuildException;
use FileSystem;
use GuzzleHttp\Client as GuzzleHttpClient;


class AcquiaCloudDatabaseTask extends \Task {

  protected $acquiaCloudConfPath = '~/.acquia/cloudapi.conf';
  protected $acquiaCloudEmail = 'me@example.com';
  protected $acquiaCloudKey = 'fake_key';
  protected $acquiaCloudEndpoint = 'https://cloudapi.acquia.com/v1';
  protected $acquiaRealm = 'prod';
  protected $acquiaSite = 'some_site';
  protected $acquiaEnv = 'dev';

  protected $overwriteExisting = FALSE;

  /**
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Init tasks.
   */
  public function init() {
    // @todo load Cloud API configuration from $this->confPath;

    $this->client = new GuzzleHttpClient([
      'base_uri' => $this->acquiaCloudEndpoint,
      'auth' => [$this->acquiaCloudEmail, $this->acquiaCloudKey],
    ]);
  }


  /**
   * Download the most recent database backup.
   */
  public function main() {
    $this->validate();

    $this->getAvailableBackups();
    $this->selectMostRecent();
    $this->deleteExistingBackup();
    $this->downloadBackup();
    $this->printPathToBackup();
  }


  /**
   * Verify required attributes (?).
   */
  public function validate() {
    if (!in_array($this->acquiaEnv, ['dev', 'test', 'prod'])) {
      throw new BuildException("env attribute must be either 'dev', 'test', or 'prod'", $this->env);
    }
  }


  public function getAvailableBackups() {
    $path = "sites/{$this->acquiaRealm}:{$this->acquiaSite}/envs/{$this->acquiaEnv}/dbs/{$this->acquiaSite}/backups.json";
    $response = $this->client->get($path);
    $backups = \GuzzleHttp\json_decode($response->getBody());
    $this->log(print_r($backups, TRUE));

    
  }

  public function selectMostRecent() {}
  public function deleteExistingBackup() {
    if ($this->overwriteExisting && $this->dest->exists()) {
      // Remove existing backup.
      $this->log("Replacing existing backup '" . $this->dest->getPath() . "'");

      if ($this->dest->delete(TRUE) === FALSE) {
        throw new BuildException("Failed to delete existing backup '$this->dest'");
      }
    }
  }
  public function downloadBackup() {}
  public function printPathToBackup() {}

}
