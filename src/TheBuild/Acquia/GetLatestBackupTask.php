<?php

namespace TheBuild\Acquia;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileWriter;

/**
 * Fetch a recent backup from Acquia.
 */
class GetLatestBackupTask extends AcquiaTask {

  /**
   * Required. Directory for storing downloaded database backups.
   *
   * @var Phing\Io\File
   */
  protected $dir;

  /**
   * Required. The Acquia Cloud hosting realm where the site is running.
   *
   * This also appears in a site's server names, as
   * 'sitename.REALM.hosting.acquia.com'.
   *   - Acquia Cloud Enterprise: 'prod'
   *   - Acquia Cloud Professional: 'devcloud'
   *
   * @var string
   */
  protected $realm;

  /**
   * Required. The Acquia Cloud site account name.
   *
   * @var string
   */
  protected $site;

  /**
   * Required. The Acquia Cloud environment.
   *
   * Generally 'dev', 'test', or 'prod', unless a site has RA or other
   * additional environments.
   *
   * @var string
   */
  protected $env;

  /**
   * Optional. The name of the database whose backup to download.
   *
   * This will correspond with the site name unless your site uses multiple
   * databases or you are running Drupal multisites.
   *
   * @var string
   */
  protected $database;

  /**
   * Optional. Maximum age of the database backup in hours.
   *
   * If there is no backup matching this age in the current backups.json, the
   * backups.json will be refreshed and the newest backup will be downloaded.
   *
   * @var int
   */
  protected $maxAge = 24;

  /**
   * Name of a property to populate with the path to the latest database backup.
   *
   * Optional parameter.
   *
   * @var string
   */
  protected $propertyName;

  /**
   * Where to store the JSON list of database backups.
   *
   * This info is downloaded from the Acquia Cloud API. The file is set to
   * 'backups.json' in the directory specified by $dir.
   *
   * @var Phing\Io\File
   */
  protected $backupsFile;

  /**
   * {@inheritdoc}
   *
   * @throws \Phing\Io\IOException
   * @throws Phing\Exception\BuildException
   * @throws \HTTP_Request2_Exception
   */
  public function main() {
    $this->validate();

    // Store the Acquia Cloud API JSON database backup records in our backups
    // directory.
    $this->backupsFile = new File($this->dir, "backups-{$this->site}-{$this->database}-{$this->env}.json");

    // Check the database backup records for entries within our time window.
    $backups = $this->getCurrentBackupRecords();

    // Have we already downloaded any of the entries in our time window?
    $downloaded_backups = [];
    foreach ($backups as $backup) {
      $filename = basename($backup['path']);
      $file = new File($this->dir, $filename);

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

    // This means that we didn't have a current record in our backups json, and
    // the Acquia Cloud API returned empty or malformed JSON.
    if (empty($newest_backup)) {
      throw new BuildException('Failed to find a backup record.');
    }

    // Download the backup if it does not yet exist on the filesystem.
    $filename = basename($newest_backup['path']);
    $file = new File($this->dir, $filename);
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
   *   Acquia backup info array.
   * @param Phing\Io\File $destination
   *   Destination file for the downloaded backup.
   *
   * @throws Phing\Exception\BuildException
   * @throws \HTTP_Request2_Exception
   * @throws \Phing\Io\IOException
   */
  protected function downloadBackup(array $backup, Phing\Io\File $destination) {
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

    $this->log("Downloaded " . intval($response->getHeader('content-length')) / 1000000 . "MB to " . $destination->getAbsolutePath());
  }

  /**
   * Get backup records that are within the desired time window.
   *
   * @return array
   *   Array of available backups within the specified timeframe.
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
   * Get the array of backup records from the Acquia Cloud API JSON output.
   *
   * Sorts records from oldest to newest.
   *
   * @param Phing\Io\File $file
   *   Temp file containing the Acquia Cloud API response.
   *
   * @return array
   *   Acquia backup info array.
   *
   * @throws Phing\Exception\BuildException
   *
   * @SuppressWarnings(PHPMD.ShortVariable)
   */
  protected function getBackupRecords(Phing\Io\File $file) {
    if ($file->exists()) {
      $backups = json_decode($file->contents(), TRUE);

      // If the backup records have loaded as an array, and the first record
      // has the property that we're using, then it is *probably* valid data.
      if (isset($backups[0]['started'])) {

        // Sort the backups by start time so that the newest is always last.
        usort($backups, function ($a, $b) {
          if ($a['started'] == $b['started']) {
            return 0;
          }
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
     *
     * @param Phing\Io\File $backups_file
     *   The file where the downloaded backup should be stored.
     *
     * @throws \HTTP_Request2_Exception
     * @throws \Phing\Io\IOException
     */
  protected function downloadBackupRecords(Phing\Io\File $backups_file) {
    $json = $this->getApiResponseBody("/sites/{$this->realm}:{$this->site}/envs/{$this->env}/dbs/{$this->database}/backups.json");

    $writer = new FileWriter($backups_file);
    $writer->write($json);
  }

  /**
   * Format the backup time to display in log messages.
   *
   * @param array $backup
   *   Acquia backup info array.
   *
   * @return string
   *   A human-readable date.
   */
  protected function formatBackupTime(array $backup) {
    $time = new \DateTime('now');
    $time->setTimestamp($backup['started']);
    return $time->format(DATE_RFC850);
  }

  /**
   * Set the Acquia realm.
   *
   * @param string $value
   *   Acquia realm.
   */
  public function setRealm(string $value) {
    $this->realm = $value;
  }

  /**
   * Set the Acquia site name.
   *
   * @param string $value
   *   Acquia site name.
   */
  public function setSite(string $value) {
    $this->site = $value;
  }

  /**
   * Set the Acquia environment name.
   *
   * @param string $value
   *   Acquia environment name.
   */
  public function setEnv(string $value) {
    $this->env = $value;
  }

  /**
   * Set the Acquia database name.
   *
   * @param string $value
   *   Set the database name.
   */
  public function setDatabase(string $value) {
    $this->database = $value;
  }

  /**
   * Set the destination directory.
   *
   * @param string $value
   *   Directory path.
   */
  public function setDir(string $value) {
    $this->dir = new File($value);
  }

  /**
   * Set the max age.
   *
   * @param int|string $value
   *   Max age in hours.
   */
  public function setMaxAge(int|string $value) {
    $this->maxAge = (int) $value;
  }

  /**
   * Set the property name.
   *
   * @param string $value
   *   Property name to use for the result.
   */
  public function setPropertyName(string $value) {
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
        throw new BuildException("$attribute attribute is required.", $this->getLocation());
      }
    }
  }

}
