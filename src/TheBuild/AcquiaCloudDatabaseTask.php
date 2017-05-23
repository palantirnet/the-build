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

  /**
   * @var bool
   */
  protected $overwriteExisting = FALSE;

  /**
   * @var PhingFile
   */
  protected $dest;

  /**
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * @var string
   */
  protected $property;

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

    $backups = $this->getAvailableBackups();
    $latest = $this->selectMostRecent($backups);
    $file = $this->downloadBackup($latest);

    $this->log(print_r($file, TRUE));

    if ($this->property) {
      $this->getProject()->setNewProperty($this->property, $file->getAbsolutePath());
    }
  }


  /**
   * Verify required attributes (?).
   */
  public function validate() {
    if (!in_array($this->acquiaEnv, ['dev', 'test', 'prod'])) {
      throw new BuildException("env attribute must be either 'dev', 'test', or 'prod'", $this->env);
    }

    if (empty($this->dest) || !$this->dest->isDirectory()) {
      throw new BuildException("The 'dest' attribute must be set to a directory.");
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

    if ($this->overwriteExisting && $file->exists()) {
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

  /**
   * Set the destination for the resource.
   * @param PhingFile $dest
   */
  public function setDest(PhingFile $dest) {
    $this->dest = $dest;
  }

  public function setAcquiaRealm($val) {
    $this->acquiaRealm = $val;
  }

  public function setProperty($val) {
    $this->property = $val;
  }

}
