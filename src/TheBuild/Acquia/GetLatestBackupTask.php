<?php

namespace TheBuild\Acquia;

use AcquiaCloudApi\Connector\Client;
use AcquiaCloudApi\Connector\Connector;
use AcquiaCloudApi\Endpoints\Applications;
use AcquiaCloudApi\Endpoints\DatabaseBackups;
use AcquiaCloudApi\Endpoints\Environments;

/**
 * Fetch a recent backup from Acquia.
 */
class GetLatestBackupTask extends \Task {

  /**
   * Required. Directory for storing downloaded database backups.
   *
   * @var \PhingFile
   */
  protected $dir;

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
   * {@inheritdoc}
   *
   * @throws \IOException
   * @throws \NullPointerException
   */
  public function main() {
    $this->validate();
    $credentials = $this->getAcquiaCloudCredentials();
    $client = $this->connectAcquiaCloud($credentials);
    $application_uuid = $this->getApplicationUuid($client);
    $env_uuid = $this->getEnvironmentsUuid($client, $application_uuid);
    $this->getLatestBackup($client, $env_uuid);
  }

  /**
   * Get acquia cloud credentials stored in the environments variables.
   *
   * @return array
   *   The array structure require to instantiate the cloud api client.
   */
  private function getAcquiaCloudCredentials() {
    if (!$api_key = getenv('ACQUIA_CLOUD_API_KEY')) {
      $this->log("Couldn't find ACQUIA_CLOUD_API_KEY env variable.");
    }

    if (!$api_secret = getenv('ACQUIA_CLOUD_API_SECRET')) {
      $this->log("Couldn't find ACQUIA_CLOUD_API_SECRET env variable.");
    }
    if (!$api_key || !$api_secret) {
      throw new \BuildException("Credentials are required.");
    }

    return [
      'key' => $api_key,
      'secret' => $api_secret,
    ];
  }

  /**
   * Set Connection to Acquia Cloud using env variables.
   */
  private function connectAcquiaCloud($credentials) {
    $connector = new Connector($credentials);
    return Client::factory($connector);
  }

  /**
   * Get latest backup from specified environment.
   */
  protected function getLatestBackup($client, $environment_uuid) {
    $backup = new DatabaseBackups($client);
    $backups = $backup->getAll($environment_uuid, $this->database);
    $filepath = $this->dir . '/' . $this->env . '_' . $this->database . '.sql.gz';
    if ($backups) {
      // file_put_contents loads the response into memory.
      // This is okay for small things like Drush aliases.
      // But not for database backups.
      // Use curl.options to stream data to disk and minimize memory usage.
      $client->addOption('sink', $filepath);
      $client->addOption('curl.options', [
        'CURLOPT_RETURNTRANSFER' => TRUE,
        'CURLOPT_FILE' => $filepath,
      ]);
      // Get latest backup.
      $backupId = $backups[0]->id;
      $this->log("Downloading backup id $backupId of database $this->database from $this->env environment $this->env");
      // Downloading the latest backup.
      if ($backup->download($environment_uuid, $this->database, $backupId)) {
        $this->log("Database was downloaded successfully in $filepath");
        return TRUE;
      }
    }
  }

  /**
   * Get all apps and return the one that belong to the specific project.
   */
  protected function getApplicationUuid($client) {
    $apps = new Applications($client);
    $applications = $apps->getAll();
    foreach ($applications as $application) {
      if ($application->name == $this->site) {
        return $application->uuid;
      }
    }
  }

  /**
   * Get all environments uuids from the project.
   */
  protected function getEnvironmentsUuid($client, $appUuid) {
    $environment = new Environments($client);
    $environments = $environment->getAll($appUuid);
    foreach ($environments as $env) {
      if ($env->name == $this->env) {
        return $env->uuid;
      }
    }
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
    $this->dir = new \PhingFile($value);
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
    foreach (['dir', 'site', 'env'] as $attribute) {
      if (empty($this->$attribute)) {
        throw new \BuildException("$attribute attribute is required.");
      }
    }
  }

}
