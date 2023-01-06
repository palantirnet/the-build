<?php
/**
 * @file GetLatestBackupTask.php
 *
 * Get the latest backup of a site from the Acquia Cloud API.
 *
 * @code
 *   <!-- Required parameters only -->
 *   <getAcquiaBackup dir="artifacts/backups" realm="devcloud" site="mysite" env="prod" />
 *   <!-- All parameters -->
 *   <getAcquiaBackup dir="artifacts/backups" realm="devcloud" site="mysite" env="test" database="multisite_db" maxAge="48" propertyName="database_backup_file" />
 * @endcode
 *
 * @copyright 2018 Palantir.net, Inc.
 */

namespace TheBuild\Acquia;

use BuildException;
use PhingFile;


class GetLatestBackupTask extends AcquiaTask {

  /**
   * Directory for storing downloaded database backups.
   *
   * Required parameter.
   * @var PhingFile
   */
  protected $dir;

  /**
   * The Acquia Cloud hosting realm where the site is running.
   *   - Acquia Cloud Enterprise: 'prod'
   *   - Acquia Cloud Professional: 'devcloud'
   *
   * This also appears in a site's server names, as 'sitename.REALM.hosting.acquia.com'.
   *
   * Required parameter.
   * @var string
   */
  protected $realm;

  /**
   * The Acquia Cloud site account name.
   *
   * Required parameter.
   * @var string
   */
  protected $site;

  /**
   * The Acquia Cloud environment. Generally 'dev', 'test', or 'prod', unless
   * a site has RA or other additional environments.
   *
   * Required parameter.
   * @var string
   */
  protected $env;

  /**
   * The name of the database whose backup to download. This will correspond
   * with the site name unless your site uses multiple databases or you are
   * running Drupal multisites.
   *
   * Optional parameter; defaults to matching $site.
   * @var string
   */
  protected $database;

  /**
   * Maximum age of the database backup in hours. If there is no backup matching
   * this age in the current backups.json, the backups.json will be refreshed
   * and the newest backup will be downloaded.
   *
   * Optional parameter.
   * @var int
   */
  protected $maxAge = 24;

  /**
   * Name of a property to populate with the path to the latest database backup.
   *
   * Optional parameter.
   * @var string
   */
  protected $propertyName;

  /**
   * Where to store the JSON list of database backups downloaded from the Acquia
   * Cloud API. This is set to 'backups.json' in the directory specified by $dir.
   * @var PhingFile
   */
  protected $backupsFile;

  /**
   * @throws \IOException
   * @throws \NullPointerException
   */
  public function main() {
    $this->validate();

    // Store the Acquia Cloud API JSON database backup records in our backups
    // directory.
    $this->backupsFile = new PhingFile($this->dir, "backups-{$this->site}-{$this->database}-{$this->env}.json");

    // Check the database backup records for entries within our time window.
    $backups = $this->getCurrentBackupRecords();

    // Have we already downloaded any of the entries in our time window?
    $downloaded_backups = [];
    foreach ($backups as $backup) {
      $filename = basename($backup['path']);
      $file = new PhingFile($this->dir, $filename);

      if ($file->exists()) {
        $downloaded_backups[] = $backup;
      }
    }

    // Pick out the newest current backup record, preferring already downloaded
    // backups.
    $newest_backup = FALSE;
    if (!empty($downloaded_backups)) {
      $newest_backup = end($downloaded_backups);
      $this->log("Using previously downloaded backup from " . $this->formatBackupTime($newest_backup) . " ({$newest_backup['id']})");
    }
    elseif (!empty($backups)) {
      $newest_backup = end($backups);
      $this->log("Using backup from " . $this->formatBackupTime($newest_backup) . " ({$newest_backup['id']})");
    }

    // If we don't have a current enough backup record, check the API directly.
    if (!$newest_backup) {
      $this->downloadBackupRecords($this->backupsFile);
      // Always return something, regardless of the time window.
      $backups = $this->getBackupRecords($this->backupsFile);
      $newest_backup = end($backups);

      $this->log("Using backup from " . $this->formatBackupTime($newest_backup) . " ({$newest_backup['id']})");
    }

    // This means that we didn't have a current record in our backups json, and the Acquia Cloud API returned empty or
    // malformed JSON.
    if (empty($newest_backup)) {
      throw new BuildException('Failed to find a backup record.');
    }

    // Download the backup if it does not yet exist on the filesystem.
    $filename = basename($newest_backup['path']);
    $file = new PhingFile($this->dir, $filename);
    if (!$file->exists()) {
      $this->log("Downloading the backup to " . $file->getAbsolutePath());
      $this->downloadBackup($newest_backup, $file);
    }
    else {
      $this->log("Existing backup found at " . $file->getAbsolutePath());
    }

    // Set the property value if a propertyName was provided.
    if ($this->propertyName) {
      $project = $this->getProject();
      $project->setNewProperty($this->propertyName, $file->getAbsolutePath());
    }
  }

  /**
   * Download a backup from Acquia Cloud.
   *
   * @param array $backup
   * @param PhingFile $destination
   * @throws \HTTP_Request2_Exception
   * @throws \HTTP_Request2_LogicException
   */
  protected function downloadBackup(array $backup, PhingFile $destination) {
    $stream = fopen($destination->getAbsolutePath(), 'wb');
    if (!$stream) {
      throw new BuildException('Can not write to ' . $destination->getAbsolutePath());
    }

    // Use an HTTP_Request2 with the Observer pattern in order to download large
    // backups.
    // @see HTTP/Request2/Observer/UncompressingDownload.php
    // @see https://cloudapi.acquia.com/#GET__sites__site_envs__env_dbs__db_backups-instance_route
    $request = $this->createRequest("/sites/{$this->realm}:{$this->site}/envs/{$this->env}/dbs/{$this->database}/backups/{$backup['id']}/download.json");
    $request->setConfig('store_body', FALSE);

    $observer = new \HTTP_Request2_Observer_UncompressingDownload($stream, 5000000000);
    $request->attach($observer);

    $response = $request->send();
    fclose($stream);

    $this->log("Downloaded " . $response->getHeader('content-length')/1000000 . "MB to " . $destination->getAbsolutePath());
  }

  /**
   * Get backup records that are within the desired time window.
   * @return array
   */
  protected function getCurrentBackupRecords() {
    try {
      $backups = $this->getBackupRecords($this->backupsFile);
    }
    catch (BuildException $e) {
      $backups = [];
    }

    $current_backups = [];

    $threshold_time = new \DateTime("-{$this->maxAge} hours");
    $backup_time = new \DateTime();

    foreach ($backups as $backup) {
      $backup_time->setTimestamp($backup['started']);
      if ($backup_time > $threshold_time) {
        $current_backups[] = $backup;
      }
    }

    return $current_backups;
  }

  /**
   * Get the array of backup records from the Acquia Cloud API JSON output,
   * sorted from oldest to newest.
   *
   * @param $file
   * @return array
   * @throws BuildException
   */
  protected function getBackupRecords($file) {
    if ($file->exists()) {
      $backups = json_decode($file->contents(), TRUE);

      // If the backup records have loaded as an array, and the first record
      // has the property that we're using, then it is *probably* valid data.
      if (isset($backups[0]['started'])) {

        // Sort the backups by start time so that the newest is always last.
        usort($backups, function($a, $b) {
          if ($a['started'] == $b['started']) { return 0; }
          return ($a['started'] < $b['started']) ? -1 : 1;
        });

        return $backups;
      }
      elseif (count($backups) === 0) {
        // The site might not have been backed up yet.
        throw new BuildException('No Acquia Cloud backups found: ' . $file->getCanonicalPath());
      }
    }
    throw new BuildException('Acquia Cloud backup records could not be loaded from JSON: ' . $file->getCanonicalPath());
  }

  /**
   * Download the latest list of backup records from the Acquia Cloud API.
   */
  protected function downloadBackupRecords(PhingFile $backups_file) {
    $json = $this->getApiResponseBody("/sites/{$this->realm}:{$this->site}/envs/{$this->env}/dbs/{$this->database}/backups.json");

    $writer = new \FileWriter($backups_file);
    $writer->write($json);
  }

  /**
   * Format the backup time to display in log messages.
   *
   * @param $backup
   * @return string
   */
  protected function formatBackupTime($backup) {
    $time = new \DateTime('now');
    $time->setTimestamp($backup['started']);
    return $time->format(DATE_RFC850);
  }

  /**
   * Setter functions.
   */
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
  public function setDir($value) {
    $this->dir = new PhingFile($value);
  }
  public function setMaxAge($value) {
    $this->maxAge = (int) $value;
  }
  public function setPropertyName($value) {
    $this->propertyName = $value;
  }

  /**
   * Verify that the required parameters are available.
   */
  protected function validate() {
    // If the Acquia database name isn't set, default to using the site name.
    if (empty($this->database)) {
      $this->database = $this->site;
    }
    // Check the build attributes.
    foreach (['dir', 'realm', 'site', 'env'] as $attribute) {
      if (empty($this->$attribute)) {
        throw new BuildException("$attribute attribute is required.", $this->location);
      }
    }
  }

}
