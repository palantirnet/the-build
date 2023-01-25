<?php
/**
 * @file AcquiaTask.php
 *
 * Abastract base task for creating Acquia Cloud API tasks.
 *
 * Loads the Acquia Cloud credentials from a JSON file and constructs
 * authenticated requests against the Cloud API.
 *
 * This class will use the credentials file at ~/.acquia/cloudapi.conf if none
 * is provided in the task call:
 *
 * @code
 *   <exampleTask credentialsFile="artifacts/cloudapi.conf" />
 * @endcode
 *
 * Extending classes may also set the 'endpoint' property if it is necessary to
 * use the v2 API instead of v1.
 *
 * @copyright 2018 Palantir.net, Inc.
 */

namespace TheBuild\Acquia;

use Phing\Task;
use Phing\Exception\BuildException;
use Phing\Io\IOException;
use HTTP_Request2;
use Phing\Io\File;

abstract class AcquiaTask extends Task {

  /**
   * Required. The Acquia Cloud credentials file containing a json array with
   * 'mail' and 'key' values.
   * @var File
   */
  protected $credentialsFile;

  /**
   * Email address associated with the Acquia Cloud access. This value is set
   * from the credentials file.
   * @var string
   */
  protected $mail;

  /**
   * Secure key associated with the Acquia Cloud access. This value is set from
   * the credentials file.
   * @var string
   */
  protected $key;

  /**
   * The Acquia Cloud API endpoint. This code is specific to version 1 of the
   * API.
   * @var string
   */
  protected $endpoint = 'https://cloudapi.acquia.com/v1';

    /**
     * Load the Acquia Cloud credentials from the cloudapi.conf JSON file.
     *
     * @throws IOException
     */
  protected function loadCredentials() {
    if (empty($this->mail) || empty($this->key)) {
      if (empty($this->credentialsFile)) {
        $this->credentialsFile = new File($_SERVER['HOME'] . '/.acquia/cloudapi.conf');
      }

      if (!file_exists($this->credentialsFile) || !is_readable($this->credentialsFile)) {
        throw new BuildException("Acquia Cloud credentials file '{$this->credentialsFile}' is not available.");
      }

      $contents = file_get_contents($this->credentialsFile);
      $creds = json_decode($contents, TRUE);

      $this->mail = $creds['mail'];
      $this->key = $creds['key'];
    }

    if (empty($this->mail) || empty($this->key)) {
      throw new BuildException('Missing Acquia Cloud API credentials.');
    }
  }

  /**
   * Build an HTTP request object against the Acquia Cloud API.
   *
   * @param $path
   * @return HTTP_Request2
   */
  protected function createRequest($path) {
    $this->loadCredentials();

    $uri = $this->endpoint . '/' . ltrim($path, '/');

    $request = new HTTP_Request2($uri);
    $request->setConfig('follow_redirects', TRUE);
    $request->setAuth($this->mail, $this->key);



    return $request;
  }

  /**
   * Example of how to query the Acquia Cloud API.
   *
   * @param $path
   * @return string
   * @throws \HTTP_Request2_Exception
   */
  protected function getApiResponseBody($path) {
    $request = $this->createRequest($path);

    $this->log('GET ' . $request->getUrl());
    $response = $request->send();
    return $response->getBody();
  }

    /**
     * @param File $file
     * @throws IOException
     */
  public function setCredentialsFile(File $file) {
    $this->credentialsFile = new File($file);
  }

}
