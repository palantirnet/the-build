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


class AcquiaCloudDatabase extends \Task {

  protected $acquiaCloudConfPath = '~/.acquia/cloudapi.conf';
  protected $acquiaCloudEmail = 'me@example.com';
  protected $acquiaCloudKey = 'fake_key';
  protected $acquiaCloudEndpoint = 'https://cloudapi.acquia.com/v1';
  protected $acquiaSite = 'prod:some_site';
  protected $acquiaEnv = 'dev';

  protected $overwriteExisting = FALSE;

  /**
   * Init tasks.
   */
  public function init() {
    // @todo load Cloud API configuration from $this->confPath;
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
   * Verify that required attributes.
   */
  public function validate() {
    if (strpos($this->acquiaSite, ':') === FALSE) {
      throw new BuildException("Your site name must include a realm prefix, and will generally look like 'prod:mysite' or 'devcloud:mysite'.");
    }

    if (!in_array($this->acquiaEnv, ['dev', 'test', 'prod'])) {
      throw new BuildException("env attribute must be either 'dev', 'test', or 'prod'", $this->env);
    }
  }


  public function getAvailableBackups() {}
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